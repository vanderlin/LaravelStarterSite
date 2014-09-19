<?php return array (

	'app' => array(
		'debug' => true,
		),


	'site-name' 				=> 'StarterSite',
	'site-password' 			=> 'demo',
	'google_creds' 				=> 'local',
	'use_google_login' 			=> false,
	'use_site_login' 			=> false,

	
	'google' => array(
	           
                'oauth_options'      => array('access_type'=>'offline', 'display'=>'popup'),
                'oauth_local_path'   => "",
                'oauth_remote_path'  => "",
                'api_key'            => "API_KEY",
                'hd'                 => 'HOSTED_DOMAIN',
                'app_name'           => 'APP_NAME',
                'scopes'             => 'SCOPES',
	
				'remote' => array (
							    'client_id' 	=> 'CLIENT_ID',
							    'client_secret' => 'CLIENT_SECRET',
							    'redirect_uri'  => 'URI'
						    ),
			 	'local' => array (
				    		'client_id' 	=> 'CLIENT_ID',
				    		'client_secret' => 'CLIENT_SECRET',
				    		'redirect_uri'  => 'URI'
				    	)
				 ),

	'database'=> array(	
				
				'connections' => array (

						'mysql' => array (
							'driver' 	=> 'mysql',
						    'host' 		=> 'localhost',
						    'database' 	=> 'dev',
						    'username' 	=> 'root',
						    'password' 	=> 'root',
						    'charset' 	=> 'utf8',
						    'collation' => 'utf8_unicode_ci',
						    'prefix' 	=> 'abc_'
						),
				)
	)

);





