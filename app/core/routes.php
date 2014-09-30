<?php

// ------------------------------------------------------------------------
// Site Password Protection - Post
// ------------------------------------------------------------------------
Route::post('site-login', function() {
	if(Input::has('site-password')) {

		if(Input::get('site-password') == Config::get('slate::site-password')) {
			Session::set('siteprotection', 'YES');
			return Redirect::back();
		}
	}
	Session::forget('siteprotection');
	return Redirect::back()->with(['errors'=>'Sorry wrong password']);

});

Route::get('email-site-password', function() {
	return View::make("slate::site.email-site-password");
});

Route::post('email-site-password', function() {
	if(Input::has('email')) {

		$email = Input::get('email');

		if(User::where('email', '=', $email)->first()) {

			Mail::send('slate::emails.test', array('key' => 'value'), function($message) use($email) {
			    $message->to($email, Config::get('slate::site-name'))->subject('Site Password');
			});
			return Redirect::back()->with(['notice'=>"An email was sent to {$email}"]);
		}
		return Redirect::back()->with(['error'=>'No user found by that email']);
	}
	return Redirect::back()->with(['error'=>'Missing email']);
});


// ------------------------------------------------------------------------
Route::group(array('before'=>'siteprotection'), function() {

	

	// --------------------------------------------------------------------------
	// Admin
	// --------------------------------------------------------------------------
	Route::group(['prefix'=>'admin', 'before'=>'auth'], function() {
		
		Route::get('/', function() {
			return View::make('slate::admin.index');
		});

		Route::get('users', function() {
			return View::make('slate::admin.index');
		});

		Route::get('themes', function() {
			return View::make('slate::admin.index');
		});

		Route::get('settings', function() {
			return View::make('slate::admin.index');
		});

		Route::get('assets', function() {
			return View::make('slate::admin.index');
		});

		Route::get('user/{id}', function($id) {
			return View::make('slate::admin.edituser', ['user'=>User::find($id)]);
		});

		
		Route::resource('roles', 'core\controllers\RolesController');
		Route::resource('permissions', 'core\controllers\PermissionsController');		
		Route::put('user/{id}', ['uses'=>'core\controllers\UsersController@editUserRoles']);
		Route::put('settings', ['uses'=>'core\controllers\AdminController@updateSettings']);
		Route::get('themes/{name}/install', ['uses'=>'core\controllers\AdminController@installTheme']);
		Route::get('themes/{id}', ['uses'=>'core\controllers\AdminController@activateTheme']);
		Route::put('themes/{id}', ['uses'=>'core\controllers\AdminController@updateTheme']);
		Route::get('themes/{id}/edit', ['uses'=>'core\controllers\AdminController@editTheme']);

	});


	// --------------------------------------------------------------------------
	// Assets
	// --------------------------------------------------------------------------	
	Route::group(['prefix'=>'images'], function() {
		Route::get('/', ['uses'=>'core\controllers\AssetsController@index']);
		Route::get('{id}/{size?}', ['uses'=>'core\controllers\AssetsController@resize']);
	});

	// ------------------------------------------------------------------------
	Route::get('assets/upload/modal', function() {
		return View::make('slate::admin.assets.upload-modal');
	});
	Route::get('assets/{id}/edit', function($id) {
		return View::make('slate::admin.assets.edit-modal', ['asset'=>Asset::find($id)]);
	});
	Route::post('assets/upload', ['uses'=>'core\controllers\AssetsController@upload']);
	Route::put('assets/{id}', ['uses'=>'core\controllers\AssetsController@edit']);
	Route::delete('assets/{id}', ['uses'=>'core\controllers\AssetsController@delete']);


	// --------------------------------------------------------------------------
	// Home
	// --------------------------------------------------------------------------	
	Route::get('/', function() {
		return View::make('slate::site.index');
	});

	// --------------------------------------------------------------------------
	// Register | Login | Google+
	// --------------------------------------------------------------------------
	Route::get('register', ['uses'=>'core\controllers\UsersController@register']);
	Route::get('login', ['uses'=>'core\controllers\UsersController@login']);
	Route::get('oauth2callback', ['uses'=>'core\controllers\GoogleSessionController@oauth2callback']);
	Route::post('link-google-account/{id}', ['uses'=>'core\controllers\GoogleSessionController@linkAccount', 'as'=>'google.link']);
	Route::post('unlink-google-account/{id}', ['uses'=>'core\controllers\GoogleSessionController@unlinkAccount', 'as'=>'google.unlink']);

	// --------------------------------------------------------------------------
	// User Confide routes
	// --------------------------------------------------------------------------
	Route::get('users/create', 'core\controllers\UsersController@create');
	Route::post('users', 'core\controllers\UsersController@store');
	Route::get('users/login', 'core\controllers\UsersController@login');
	Route::post('users/login', ['uses'=>'core\controllers\UsersController@doLogin', 'as'=>'users.login']);
	Route::get('users/confirm/{code}', 'core\controllers\UsersController@confirm');
	Route::get('users/forgot_password', 'core\controllers\UsersController@forgotPassword');
	Route::post('users/forgot_password', 'core\controllers\UsersController@doForgotPassword');
	Route::get('users/reset_password/{token}', 'core\controllers\UsersController@resetPassword');
	Route::post('users/reset_password', 'core\controllers\UsersController@doResetPassword');
	Route::get('users/logout', 'core\controllers\UsersController@logout');
	Route::put('users/{id}', ['uses'=>'core\controllers\UsersController@updateProfile', 'before'=>'auth', 'as'=>'user.update']);

	// --------------------------------------------------------------------------
	// Profiles & Users
	// --------------------------------------------------------------------------
	Route::group(array('before' => 'auth'), function() {

		Route::get('me', function() {
			return View::make('slate::site.user.profile', ['user'=>Auth::getUser()]);
		});

	});

	// ------------------------------------------------------------------------
	Route::get('users/{id}', ['uses'=>'core\controllers\UsersController@show']);


});

