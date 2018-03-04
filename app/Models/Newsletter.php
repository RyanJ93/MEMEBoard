<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\MEME;
use App\Models\Comment;
use App\Models\Vote;
use App\Models\User;
use App\Models\Category;

//Use this namespace to access to the trait "DispatchesJobs".
use Illuminate\Foundation\Bus\DispatchesJobs;

use Log;
use Queue;
use Mail;

class Newsletter extends Model{
	//Use this trait when you need to dispatch jobs to the Laravel queue from a class that is not extending the "Controller" class, basically when you are not running the dispatcher within a controller.
	use DispatchesJobs;
	
	/**
	* @var string $table A string containing the name of the table mapped by this model.
	*/
    protected $table = 'newsletter';
    
    /**
	* @var string $primaryKey A string containing the name of the primary key of the table mapped by this model.
	*/
	protected $primaryKey = 'email';
	
	/**
	* @var array $casts An associative array of strings containg as key the field name and as value the data type that the field should be casted into (Laravel casts primary keys into "int", in this case a string must be returned).
	*/
	protected $casts = array(
		'email' => 'string'
	);
	
	/**
	* Sends a MEME to all users subscribed to the newsletter.
	*
	* @param MEME $meme An instance of the class "App\Models\MEME" representing the MEME that shall be sent.
	*/
	public static function send(MEME $meme){
		if ( $meme === NULL ){
			throw new \InvalidArgumentException('Invalid MEME.');
		}
		try{
			dispatch(new \App\Jobs\NewsletterQueue($meme));
		}catch(\Exception $ex){
			if ( env('APP_DEBUG', false) === true ){
				Log::debug($ex->getFile() . ': ' . $ex->getLine() . ': ' . $ex->getMessage());
			}
			throw new \Exception('An error occurred while sending the MEME.');
		}
	}
}