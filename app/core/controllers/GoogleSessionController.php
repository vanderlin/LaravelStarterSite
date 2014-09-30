<?php namespace core\controllers;

use Session;
use Config;
use Google_Client;
use Request;
use Input;
use Redirect;
use User;
use Hash;
use Asset;
use Role;
use Auth;
use URL;
use File;

class GoogleSessionController extends BaseController {

	// ------------------------------------------------------------------------
	static function getCreds() {
		
		$creds = null;
		$creds_file = [];
		$google_creds = Config::get('slate::google');
		$env = Config::getEnvironment();
		
		$jsonfile = null;
		if($env == 'local' && $google_creds['oauth_local_path']) {
			$jsonfile = GoogleSessionController::loadCredentialsFile($google_creds['oauth_local_path']);
		}
		else if($google_creds['oauth_remote_path']) {
			$jsonfile = GoogleSessionController::loadCredentialsFile($google_creds['oauth_remote_path']);
		}
		if($jsonfile) {
			$google_creds = array_merge($google_creds, $jsonfile['web']);
		}

		$creds_path = Config::get('slate::google_creds', 'remote');	
		$creds 		= Config::get('slate::google.'.$creds_path);

		$obj = array_merge($google_creds);


		$obj = (object)$obj;
		return $obj;
	}

	// ------------------------------------------------------------------------
	private static function loadCredentialsFile($path) {
		$json_content = File::get($path);
		return $json_content ? json_decode($json_content, true) : [];
	}

	// ------------------------------------------------------------------------
	public static function getClient() {
		
		$creds  = GoogleSessionController::getCreds();
		$client = new Google_Client();
		$env = Config::getEnvironment();


		if($env == 'local' && $creds->oauth_local_path) {
			$client->setAuthConfigFile($creds->oauth_local_path);
		}
		else if($creds->oauth_remote_path) {
			$client->setAuthConfigFile($creds->oauth_local_path);
		}
		else {	
			$client->setApplicationName($creds->app_name);
			$client->setClientId($creds->client_id);
			$client->setClientSecret($creds->client_secret);
			$client->setRedirectUri($creds->redirect_uri); 	// <--- huh?
		}
		


		$client->addScope("https://www.googleapis.com/auth/userinfo.profile");
		$client->addScope("https://www.googleapis.com/auth/userinfo.email");
		$client->setScopes(["openid", "profile", "email", $creds->scopes]);

		$scopes = explode(",", $creds->scopes);
		$client->setScopes($scopes);


		return $client;
	}

	// ------------------------------------------------------------------------
	public static function getState() {
		
		if(Session::has('state') == false) {
			$state = md5(rand());
			Session::put('state', $state);
		}
		return Session::get('state');
	}

	// ------------------------------------------------------------------------
	static function generateGoogleLoginButton($opt_options=array()) {
		
		$default_options = array('data-width'=>'standard', 'data-theme'=>'dark', 'data-callback'=>'onSignInCallback');
		$options = array_merge($default_options, $opt_options);
		$creds = GoogleSessionController::getCreds();

		return '<button class="g-signin"
					data-scope="'.$creds->scopes.'"
					data-requestvisibleactions="http://schemas.google.com/AddActivity"
					data-clientId="'.$creds->client_id.'"
			        data-accesstype="offline"
					data-callback="'.$options['data-callback'].'"
					data-theme="'.$options['data-theme'].'"
					data-width="'.$options['data-width'].'"
					data-cookiepolicy="single_host_origin">
				</button>';
	}

	// ------------------------------------------------------------------------
	static public function getOAuthOptions($opts = array()) {
		$creds = GoogleSessionController::getCreds();
		$other_opts = array();
		if(array_key_exists('hd', $creds)) $other_opts['hd'] = $creds->hd;
		if(is_array($opts)) {
			$other_opts = array_merge($other_opts, $opts);
		}
		return array_merge(Config::get('slate::google.oauth_options'), $other_opts);
	}

	// ------------------------------------------------------------------------
	static function generateOAuthLink($opt_options=array(), $state=null) {
		
		$creds = GoogleSessionController::getCreds();
		$client = GoogleSessionController::getClient();

		if($creds->oauth_local_path) {
			$client->setAuthConfigFile($creds->oauth_local_path);
		}
		/*
		$client->setApplicationName($creds->app_name);
		$client->setClientId($creds->client_id);
		$client->setClientSecret($creds->client_secret);
		
		$client->setRedirectUri($creds->redirect_uri); 	// <--- huh?
		*/
		//$client->setRedirectUri('postmessage');				// <--- huh?

		$url = $client->createAuthUrl();

		if(is_array($opt_options)===false) {
			$opt_options = (array)$opt_options;
		}
		$default_options = array();
		$options = array_merge($default_options, $opt_options);
		if($state!=null) $options['state'] = $state;

		foreach ($options as $key => $value) {
			if($key == 'access_type') {
				$client->setAccessType($value);
			}
			else {
				$url .= '&'.$key.'='.$value;	
			}
		}
		return $url;
	}

	// ------------------------------------------------------------------------
	/*static function getClient() {
		$creds = GoogleSessionController::getCreds();
		$client = new Google_Client();
		$client->setApplicationName($creds->app_name);
		$client->setClientId($creds->client_id);
		$client->setClientSecret($creds->client_secret);
		$client->setScopes($creds->scopes);

		// $client->setRedirectUri($creds->redirect_uri); 	// <--- huh?
		//$client->setRedirectUri('postmessage');				// <--- huh?
		$client->setRedirectUri($creds->redirect_uri); 	// <--- huh?

		$client->addScope("https://www.googleapis.com/auth/userinfo.profile");
		$client->addScope("https://www.googleapis.com/auth/userinfo.email");



		return $client;
	}*/

	// ------------------------------------------------------------------------
	static function doesUserExistInEmails($emails) {
		foreach ($emails as $email) {
			$e = $email['value'];
			$u = User::findFromEmail($e);
			if($e != null) {
				return $u;
			}
		}
		return null;
	}

	// ------------------------------------------------------------------------
	public static function findUserFromGoogleID($id) {
        return \User::where('google_id', '=', $id)->first();
	}



	// ------------------------------------------------------------------------
	public function oauth2callback() {
		
		$state = Input::get('state');
		
		if($state == 'link') 		return $this->linkAccountCallback();
		if($state =='registering')	return $this->register();
		
		return $this->signin();	
	}

	// ------------------------------------------------------------------------
	public function linkAccountCallback() {
		
		$user = Auth::user();
		$code = Input::get('code');
		if($code && $user) {


			$wantsJson 	= Request::wantsJson();
			$creds 		= GoogleSessionController::getCreds();
			$client 	= GoogleSessionController::getClient();

			 // Exchange the OAuth 2.0 authorization code for user credentials.
	        $client->authenticate($code);
			$token = json_decode($client->getAccessToken());
			$attributes = $client->verifyIdToken($token->id_token, $creds->client_id)->getAttributes();


			$oauth2 = new \Google_Service_Oauth2($client);
			$google_user = $oauth2->userinfo->get();
			$email = $google_user->email;
			$username = explode("@", $email)[0];
			
			// get google account info
			$user->google_token = json_encode($token);
			$user->google_id = $google_user->id;

			if(empty($user->firstname)) $user->firstname = $google_user->givenName;
			if(empty($user->lastname)) $user->lastname  = $google_user->familyName;

			if($user->hasDefaultProfileImage()) $this->saveGoogleProfileImage($google_user, $user);

			if($user->save()) {
				$back_url = 'users/'.$user->username;
				Auth::login($user);			
	        	return Redirect::to($back_url);	
			}
			else {
				return $wantsJson ? Response::json(['errors'=>$user->errors()->all()]) : Redirect::to('/')->with(['errors'=>$user->errors()->all()]);
			}


			// return Response::json(['errors'=>$user->givenName]);

		}
		
		return $wantsJson ? Response::json(['errors'=>'An error occurred']) : Redirect::to('/')->with(['errors'=>'An error occurred']);
	}

	// ------------------------------------------------------------------------
	public function linkAccount($id) {


		$user 		= User::find($id);
		$wantsJson 	= Request::wantsJson();
		$creds 		= GoogleSessionController::getCreds();
		$client 	= GoogleSessionController::getClient();

		if($user && $user->isMe()) {

 			$url = GoogleSessionController::generateOAuthLink(Config::get('slate::google_url_options'), 'link');
 			return Redirect::to($url);

		}
		else {
			$errors = ['errors'=>['No user found']];
			return $wantsJson ? Response::json($errors) : Redirect::to('/')->with($errors);
		}
		return $user;

	}

	// ------------------------------------------------------------------------
	public function unlinkAccount($id) {


		$user 		= User::find($id);
		$wantsJson 	= Request::wantsJson();

		if($user && $user->isMe()) {
	 		$user->google_token = "";
	 		$user->google_id = "";
	 		$user->save();
	 		return Redirect::back();	
		}
		else {
			$errors = ['errors'=>['No user found']];
			return $wantsJson ? Response::json($errors) : Redirect::to('/')->with($errors);
		}
	}
	
	// ------------------------------------------------------------------------
	public function saveGoogleProfileImage(&$google_user, &$user) {

		// profile image
		$image_url = $google_user->picture;

		if($image_url) {
	    	$image_url_parts = explode('?', $image_url);
	    	$image_url = $image_url_parts[0];
	    	$id = $user->id;
	    	
	    	$image_name =  $user->username.'_'.$id.'.jpg';
	    	$save_path  = 'assets/content/users';

	    	
	    	$userImage = new Asset;
	    	$userImage->saveRemoteImage($image_url, $save_path, $image_name);
	    	$userImage->save();
	    	$user->profileImage()->save($userImage);
		}

	}

	// ------------------------------------------------------------------------
	public function register($isLinking=false) {
		
		$wantsJson = Request::wantsJson();
		$creds = GoogleSessionController::getCreds();
		$client = GoogleSessionController::getClient();


		$code = Input::get('code');
		if($code) {


			 // Exchange the OAuth 2.0 authorization code for user credentials.
	        $client->authenticate($code);
			$token = json_decode($client->getAccessToken());
			$attributes = $client->verifyIdToken($token->id_token, $creds->client_id)->getAttributes();


			$oauth2 = new \Google_Service_Oauth2($client);
			$google_user = $oauth2->userinfo->get();
			$email = $google_user->email;
			$username = explode("@", $email)[0];
			
			// return Response::json(['errors'=>$user->givenName]);


		
			if($google_user->hd != 'ideo.com') {
				$errors = ['errors'=>[Config::get('config.site-name').' is for IDEO only']];
				return $wantsJson ? Response::json($errors) : Redirect::to('register')->with($errors);
			}
			
		    $user = new User;
		    $user->username  = $username;
		    $user->email 	 = $email;
			$password 		 = Hash::make($username); // <-- temp...

			$user->firstname = $google_user->givenName;
			$user->lastname  = $google_user->familyName;

			$user->password 			 = $password;
			$user->password_confirmation = $password;

			$user->confirmation_code 	 = md5($user->username.time('U'));
			$user->google_token = json_encode($token);

		    if($user->save()) {

				// profile image
				$this->saveGoogleProfileImage($google_user, $user);

				// Default Roles
	        	if($username == 'tvanderlin' || $username == 'Admin') {
					$adminRole = Role::where('name', '=', 'Admin')->first();
					$user->attachRole($adminRole);
				}
				else {
		            $role = $role = Role::where('name', '=', 'Writer')->first();
		            if($role) {
		            	$user->attachRole($role);
		            	$user->save();
					}
				}

			
				$back_url = 'users/'.$username;
				Auth::login($user);			
	        	return Redirect::to($back_url);		

			}
			else {
				return $wantsJson ? Response::json(['errors'=>$user->errors()->all()]) : Redirect::to('register')->with(['errors'=>$user->errors()->all()]);
			}
				
			
	        return Response::json(['data'=>$token, 'attr'=>$attributes, 'user'=>$user]);
			
		}
		
		return $wantsJson ? Response::json(['errors'=>['Missing OAuth Code']]) : Redirect::to('register')->with(['errors'=>$user->errors()->all()]);
	}

	// ------------------------------------------------------------------------
	public function signin() {
		
		$wantsJson = Request::wantsJson();
		$creds = GoogleSessionController::getCreds();
		$client = GoogleSessionController::getClient();

		$code = Input::get('code');

		if($code) {


			 // Exchange the OAuth 2.0 authorization code for user credentials.
	        $client->authenticate($code);
			$token = json_decode($client->getAccessToken());
			$attributes = $client->verifyIdToken($token->id_token, $creds->client_id)->getAttributes();

			$oauth2 = new \Google_Service_Oauth2($client);
			$google_user = $oauth2->userinfo->get();
			$email = $google_user->email;
			$username = explode("@", $email)[0];

			if($google_user) {

				$u = GoogleSessionController::findUserFromGoogleID($google_user->id);
			
				if($u != null) {
					if(empty($u->google_token)) {
						$u->google_token = json_encode($token);
						$u->save();
					}
					Auth::login($u);
					$back_url = URL::to('me');
					$resp = ['notice'=>'Welcome '.$u->username, 'back_url'=>$back_url];
					return $wantsJson ? Response::json($resp) : Redirect::to($back_url)->with(['notice'=>'Welcome '.$u->username]);
				}
				return $wantsJson ? Response::json(['error'=>'No user found with that id']) : Redirect::back()->with(['error'=>'No user found with that id']);

				
			}
			

			

			$errors = ['error'=>$email.' is not registered with '.Config::get('slate::site-name')];
			return $wantsJson ? Response::json($errors) : Redirect::to('login')->with($errors);
		}
		
		return $wantsJson ? Response::json(['error'=>'Missing OAuth Code']) : Redirect::to('login')->with(['error'=>'Missing OAuth Code']);
	}

	// ------------------------------------------------------------------------
	/**
	 * Display a listing of the resource.
	 * GET /googlesession
	 *
	 * @return Response
	 */
	public function index()
	{
		//
	}

	/**
	 * Show the form for creating a new resource.
	 * GET /googlesession/create
	 *
	 * @return Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created resource in storage.
	 * POST /googlesession
	 *
	 * @return Response
	 */
	public function store()
	{
		//
	}

	/**
	 * Display the specified resource.
	 * GET /googlesession/{id}
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 * GET /googlesession/{id}/edit
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 * PUT /googlesession/{id}
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 * DELETE /googlesession/{id}
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		//
	}

}