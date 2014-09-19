<?php namespace core\commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use File;

class PublishSlateCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'slate:publish';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Publish assets and configuration';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}


	// ------------------------------------------------------------------------
	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire() {

		$override = $this->option('override');
		if($override=='yes'||$override=="true"||$override==1) {
			if($this->confirm('Are you sure you want to override the namespace and copy routes |yes|no|')) {
				$this->comment("All Views and Routes copied over");
				
				File::copy(app_path().'/routes.php', app_path().'/backup_routes.php');
				File::copy(app_path().'/core/routes.php', app_path().'/routes.php');

				$r = File::get(app_path().'/routes.php');
				$contents = str_replace("slate::", "", $r);
				File::put(app_path().'/routes.php', $contents);


				$views = File::copyDirectory(app_path().'/core/views', app_path().'/views');

				$files = File::allFiles(app_path().'/views');
				foreach ($files as $f) {
					if(File::isFile($f)) {
						$contents = $f->getContents();
						$contents = str_replace("slate::", "", $contents);
						
						if(File::put(app_path().'/views/'.$f->getRelativePathname(), $contents)) {
							$this->line("File Saved - $f");
						}

					}
				}
				
				//File::copyDirectory(app_path().'/core/views', app_path, options)
				//$this->call('view:publish', ['package'=>'vanderlin/slate', '--path'=>'app/core/views']);
			}	
			return;
		}
		
		if($this->confirm('Are you sure you want to publish config/assets? |yes|no|')) {
			$this->call('config:publish', ['package'=>'vanderlin/slate', '--path'=>'app/core/config']);
	        $this->info( "publishing complete!" );
    	}
    	if($this->confirm('Are you sure you want to publish views? |yes|no|')) {
			$this->call('view:publish', ['package'=>'vanderlin/slate', '--path'=>'app/core/views']);
	        $this->info( "publishing complete!" );
    	}
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments() {
		return array(
		);
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions() {

		return array(
			array('override', null, InputOption::VALUE_OPTIONAL, 'Override the slate namespace and copy over all routes/views.', null),
			// array('example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null),
		);
	}

}
