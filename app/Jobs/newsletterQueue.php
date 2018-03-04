<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use App\Models\Newsletter;

use Log;
use Queue;
use Mail;

class NewsletterQueue implements ShouldQueue{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
	
	/**
	* @var MEME $meme An instance of the class "App\Models\MEME" representing the MEME that shall be sent to the sewsletter's mailing list.
	*/
	protected $meme = NULL;

	/**
	* Create a new job instance.
	*
	* @param MEME $meme An instance of the class "App\Models\MEME" representing the MEME that shall be sent to the sewsletter's mailing list.
	*
	* @return void
	*
	* @throws InvalidArgumentException If an invalid MEME is given.
	*/
    public function __construct(\App\Models\MEME $meme){
        if ( $meme === NULL || $meme->id === NULL ){
	        throw new \InvalidArgumentException('Invalid MEME.');
        }
        $this->meme = $meme;
    }

	/**
	* Execute the job.
	*
	* @return void
	*
	* @throws BadMethodCallException If no MEME were defined.
	*/
    public function handle(){
        try{
		    if ( $this->meme === NULL ){
			    throw new \BadMethodCallException('No MEME defined.');
		    }
		    $url = env('APP_URL');
		    $title = 'Your newsletter from ' . env('APP_NAME') . '.';
			$params = array(
				'globalTitle' => $title,
			    'title' => $this->meme->title,
			    'text' => $this->meme->text,
			    'image' => $this->meme->relativePath === true ? ( $url . '/' . $this->meme->processedPath ) : $this->meme->processedPath,
			    'memeLink' => $url . '/memes/' . $this->meme->id,
			    'ratio' => $this->meme->ratio,
			    'categories' => array(),
			    'user' => $this->meme->creator !== NULL ? array(
				    'id' => $this->meme->creator->id,
				    'name' => $this->meme->creator->fullName
			    ) : NULL
		    );
		    foreach ( $this->meme->categories as $key => $value ){
			    $params['categories'][] = $value->name;
		    }
		    foreach ( Newsletter::orderBy('created_at', 1)->get() as $key => $value ){
			    $address = $value->email;
			    $params['token'] = $value->token;
			    Mail::send('mail/newsletter', $params, function($message) use ($address, $title){
				    $message->to($address)->subject($title);
			    });
		    }
        }catch(\Exception $ex){
	        if ( env('APP_DEBUG') === true ){
				Log::debug($ex->getFile() . ': ' . $ex->getLine() . ': ' . $ex->getMessage());
			}
        }
    }
}