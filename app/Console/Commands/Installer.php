<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;

use Artisan;

class Installer extends Command{
	/**
	* The name and signature of the console command.
	*
	* @var string A string containing the syntax of this command.
	*/
    protected $signature = 'install {--seed}';

    /**
	* The console command description.
	*
	* @var string A string containing the description of this command.
	*/
    protected $description = 'This command allows you to set up the project on your machine, including database creation.';

    /**
	* Create a new command instance.
	*/
    public function __construct(){
        parent::__construct();
    }

	/**
	* Execute the console command.
	*/
    public function handle(){
	    if ( function_exists('shell_exec') === false ){
		    echo 'You need to enable the function "shell_exec" before running installer.' . PHP_EOL;
		    echo 'Consider to keep enabled this function for this project in order to be able to allow video uploads, this function is used to handle videos using the command "ffmpeg".' . PHP_EOL;
		    return;
	    }
        echo 'Starting up installer...' . PHP_EOL;
        $options = $this->options();
        $migrations = scandir(dirname(__FILE__) . '/../../../database/migrations/');
        $queue = false;
        foreach ( $migrations as $key => $value ){
	        if ( preg_match('/create_jobs_table/i', $value) ){
		        $queue = true;
		        break;
	        }
        }
        if ( $queue === false ){
	        echo 'Creating queue table...' . PHP_EOL;
	        if ( Artisan::call('queue:table') < 0 ){
		        echo 'An error occurred while creating the table for the queue, aborting.' . PHP_EOL;
				return;
	        }
			echo 'Queue\'s table created' . PHP_EOL;
        }else{
	        echo 'Queue\'s table already existing.' . PHP_EOL;
        }
        echo 'Creating database tables...' . PHP_EOL;
        if ( Artisan::call('migrate') < 0 ){
	        echo 'An error occurred while creating tables, aborting.' . PHP_EOL;
	        return;
        }
        echo 'Tables created.' . PHP_EOL;
        if ( isset($options['seed']) === true && $options['seed'] === true ){
	        echo 'Seeding the database...' . PHP_EOL;
	        $result = Artisan::call('db:seed', array(
		        '--class' => 'SeedMEMETable'
	        ));
	        if ( $result < 0 ){
		        echo 'An error occurred while seeding the database, aborting.' . PHP_EOL;
				return;
	        }
	        echo 'Database seeded.' . PHP_EOL;
        }
        echo 'Starting queue daemon...' . PHP_EOL;
        if ( Artisan::call('queue:restart') < 0 ){
	        echo 'An error occurred while starting queue, aborting.' . PHP_EOL;
			return;
        }
        echo 'Queue started.' . PHP_EOL;
        echo 'Creating MEMEs\' directory...' . PHP_EOL;
        $dir = env('MEME_PATH', NULL);
        if ( $dir === NULL ){
	        echo 'No directory for MEMEs has been defined in ".env", aborting.' . PHP_EOL;
	        return;
        }
        $dir = dirname(__FILE__) . '/../../../storage/app/public/' . $dir;
        if ( file_exists($dir) === false ){
	        if ( mkdir($dir) === false ){
		        echo 'Unable to create directory for MEMEs, aborting.' . PHP_EOL;
				return;
	        }
        }
        echo 'MEMEs\' directory created successfully.' . PHP_EOL;
        echo 'Creating symlink to MEMEs\' folder...' . PHP_EOL;
        $link = dirname(__FILE__) . '/../../../public/' . env('MEME_PATH');
        if ( file_exists($link) === true ){
	        echo 'Symlink already existing.' . PHP_EOL;
        }else{
	        if ( $dir === '' || symlink($dir, dirname(__FILE__) . '/../../../public/' . env('MEME_PATH')) === false ){
		        echo 'Unable to create symlink, aborting.' . PHP_EOL;
				return;
	        }
	        echo 'Symlink created successfully.' . PHP_EOL;
        }
        $result = @shell_exec('ffmpeg -version');
        if ( $result === NULL || strpos(strtolower($result), 'no command') !== false ){
	        echo '"ffmpeg" appears to be not installed, this means that your project will not be able to handle videos, uploading a video will result in an error during MEME creation.' . PHP_EOL;
        }
        echo 'Project installed.' . PHP_EOL;
    }
}