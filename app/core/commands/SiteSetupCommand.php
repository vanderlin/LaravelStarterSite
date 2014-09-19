<?php namespace core\commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Config;
use ConfigHelper;
use DB;

class SiteSetupCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'slate:setup';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Setup the Laravel starter site.';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct() {
		parent::__construct();
	}

	// ------------------------------------------------------------------------
	static function saveConfig() {

	}

	// ------------------------------------------------------------------------
	// Migrate the database
	// ------------------------------------------------------------------------
	public function migrateTheDatabase() {
		
		
		$this->call('migrate', ['--path'=>'app/core/migrations/']);

	}

	// ------------------------------------------------------------------------
	public function createGoogleCredentials() {
		$google_creds = array();

		$google_creds['client_id'] = $this->ask('Client ID? ');			
		if($google_creds['client_id']) { 
			//$this->line(var_export($google_creds)."\n");
		}

		$google_creds['client_secret'] = $this->ask('Client secret? ');			
		if($google_creds['client_secret']) { 
			//$this->line(var_export($google_creds)."\n");
		}

		$google_creds['redirect_uri'] = $this->ask('Redirect URI? ');			
		if($google_creds['redirect_uri']) { 
			//$this->line(var_export($google_creds)."\n");
		}

		return $google_creds;
	}

	// ------------------------------------------------------------------------
	public function askQuestion($question, $closure) {
		$v = $this->ask($question);
		
		if($v!=NULL && is_callable($closure)) {
			call_user_func($closure, $v);
		}
	}

	// ------------------------------------------------------------------------
	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire() {
		
		
		$this->comment("*************************************");
		$this->comment('*  Setting up Laravel Starter Site  *');
		$this->comment("*************************************");

		$this->call('config:publish', ['package'=>'vanderlin/slate', '--path'=>'app/core/config/']);

        // google creds
        if($this->confirm('Use Google+ Auth? [yes|no]', true)) {

        	ConfigHelper::save('slate::use_google_login', true);
        	
        	$google_creds = Config::get('slate::google');

        	$this->askQuestion('Google API Key?', function($value) use(&$google_creds) {
        		$google_creds['api_key'] = $value;
        	});
    		$this->line($google_creds['api_key']);


        	$this->askQuestion('Google application name?', function($value) use(&$google_creds) {
        		$google_creds['app_name'] = $value;
        	});
    		$this->line($google_creds['app_name']);


        	$this->askQuestion('Hosted Domain? This restricts any domain but the one entered.', function($value) use(&$google_creds) {
        		$google_creds['hd'] = $value;
        	
        	});
			$this->line($google_creds['hd']);

        	$google_scopes = ['https://www.googleapis.com/auth/plus.profile.emails.read',
        					  'https://www.googleapis.com/auth/plus.login',
							  'https://www.googleapis.com/auth/plus.me'];

			$this->line("\n");
        	$scope_choices = $this->choice('OAuth Scopes: (separate numbers with a comma) ', $google_scopes, 0, null, true);
        	if($scope_choices) {
        		$google_creds['scopes'] = implode(',', $scope_choices);	
        	}
    		$this->line($google_creds['scopes']);

			$this->line("\n");
        	if($this->confirm("Set Google JSON Credentials. (you can download these files from console.developers.google.com) [yes|no]", true)) {
        		
        		// local file 
	        	$this->askQuestion('Local JSON file path (This is a relative path to public. ie: assests/google/local.json)', function($value) use(&$google_creds) {
	        		$google_creds['oauth_local_path'] = $value;
	        	});
	        	$this->line($google_creds['oauth_local_path']);

				// remote file 
        		$this->askQuestion('Remote JSON file path (This is a relative path to public. ie: assests/google/remote.json)', function($value) use(&$google_creds) {
	        		$google_creds['oauth_remote_path'] = $value;
	        	});
        		$this->line($google_creds['oauth_remote_path']);
	        	

        	}
        	else {

        		$this->line("\n");
				if($this->confirm("Enter local Google credentials? [yes|no]", true)) {
					$google_creds['local'] = $this->createGoogleCredentials();
				}
				$this->line("\n");
				if($this->confirm("Enter remote Google credentials? [yes|no]", true)) {
					$google_creds['remote'] = $this->createGoogleCredentials();
				}
				
				$this->comment("Current Local Google+ Settings:");
				foreach ($google_creds['local'] as $key => $value) {
					$this->line("{$key} = {$value}");
				}
				$this->comment("Current Remote Google+ Settings:");
				foreach ($google_creds['remote'] as $key => $value) {
					$this->line("{$key} = {$value}");
				}
			}

			// save the settings
        	ConfigHelper::save("slate::google", $google_creds);

			// ConfigHelper::setAndSave('google-config.local', $google_creds['local'], 'production');
			// ConfigHelper::setAndSave('google-config.remote', $google_creds['remote'], 'production');

        }
         
		 // create a admin user?
        if($this->confirm('Setup local database credentials? [yes|no]', true)) {
			
			$local_db_path = 'database.connections.mysql';
			$creds = Config::get($local_db_path); // default data

			$this->comment("Current Local Settings:");
			foreach ($creds as $key => $value) {
				$this->line("{$key} = {$value}");
			}

			


			 // array (
		    //   'driver' => 'mysql',
		    //   'host' => 'localhost',
		    //   'database' => 'dev',
		    //   'username' => '*** this is new ***',
		    //   'password' => 'root',
		    //   'charset' => 'utf8',
		    //   'collation' => 'utf8_unicode_ci',
		    //   'prefix' => 'site_',
		    // ),

			$host = $this->ask("Hostname? ");			
			if($host) { 
				$creds['host'] = $host;
			}


			$database = $this->ask('Database name? ');			
			if($database) { 
				$creds['database'] = $database;
			}

			$username = $this->ask('Database username? ');			
			if($username) { 
				$creds['username'] = $username;
			}

			$password = $this->ask('Database password? ');			
			if($password) { 
				$creds['password'] = $password;
			}

			$prefix = $this->ask('Database prefix? ');			
			if($prefix) { 
				$creds['prefix'] = $prefix;
			}
			else if(empty($creds['prefix'])) {
				$this->comment("If you do not set a prefix you may run into foriegn key conflics.");
				$prefix = $this->ask('Database prefix? ');			
				if($prefix) { 
					$creds['prefix'] = $prefix;
				}
			}


			$this->comment("\n*******************************");
			$this->comment("   Local Database Credentials    ");
			$this->comment("*******************************");
			foreach ($creds as $key => $value) {
				$this->line("{$key} = {$value}");
			}

			Config::set("database.connections.mysql", $creds);
			Config::set("slate::database.connections.mysql", $creds);
			ConfigHelper::save('slate::database.connections.mysql', $creds);
			
			// setup db
			DB::connection("mysql");
						
        }        

		 // create a admin user?
        if($this->confirm('Migrate the database? [yes|no]', true)) {
			$this->comment("\nMigrating the database...\n");        	
			$this->migrateTheDatabase();
        }

		$sitename = $this->option('sitename');

		if( $sitename == NULL) {
			$sitename = $this->ask('What is the name of this site? ');
			if($sitename) {
				Config::set('slate::site-name', $sitename);
				ConfigHelper::save('slate::site-name');				
			}
			else {
				$sitename = Config::get('slate::site-name');	
			}
		}
		$sitename = empty($sitename) ? 'Laravel Starter Site' : $sitename;
        $this->comment("\nStarter site: {$sitename}\n");



        // site password
        $use_site_pass = $this->confirm('Use a site password? [yes|no]', true);
        $sitepassword  = "";
        if($use_site_pass) {
        	$sitepassword = $this->ask('Enter a password for the site? ');
			if($sitepassword) { 
				$this->comment("\nPassword to enter site is: {$sitepassword}\n");        	
			}
        }

        Config::set('slate::site-password', $sitepassword?$sitepassword:'');
		Config::set('slate::use_site_login', $use_site_pass?true:false);

		ConfigHelper::save('slate::site-password');
		ConfigHelper::save('slate::use_site_login');				
	
        

   

        // create a admin user?
        if($this->confirm('Do you want to create an Admin user? [yes|no]', true)) {
        	$this->call('slate:adduser', array('--admin' => 'yes'));
        }

		$this->comment("\n*******************************");
		$this->comment('          All Done!			');
		$this->comment("*******************************\n");

	}	

	// ------------------------------------------------------------------------
	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			// array('reset', InputArgument::OPTIONAL, 'Reset the site.', null)
			//array('example', InputArgument::REQUIRED, 'An example argument.'),
		);
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions() {
		return array(
			array('sitename', null, InputOption::VALUE_OPTIONAL, 'Name of this site.', null),
		);
	}

}
