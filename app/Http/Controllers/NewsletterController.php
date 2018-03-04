<?php
namespace App\Http\Controllers;

require dirname(__FILE__) . '/../../../resources/library.php';

use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Comment;
use App\Models\Vote;
use App\Models\Newsletter;
use App\Models\Category;

use Log;

class NewsletterController extends Controller{
	/**
	* Adds an e-mail address to the newsletter's mailing list.
	*
	* @param Request $request An instance of the class "Illuminate\Http\Request" representing the request originated the current client.
	*
	* @return JsonResponse An isntance of the class "Illuminate\Http\Response" representing the response that will be sent to the client.
	*/
    public function subscribe(Request $request): \Illuminate\Http\JsonResponse{
	    try{
		    $params = $request->only(array('email'));
		    if ( isset($params['email']) === false || $params['email'] === '' || is_string($params['email']) === false || filter_var($params['email'], \FILTER_VALIDATE_EMAIL) === false ){
			    return \MEMEBoard\Utils::returnError(1, 'Invalid e-mail address.');
		    }
		    if ( Newsletter::where('email', '=', $params['email'])->exists() === true ){
			    return \MEMEBoard\Utils::returnSuccess(2, 'This e-mail address is already part of the mailing list.');
		    }
		    $newsletter = new Newsletter();
		    $newsletter->email = $params['email'];
		    $newsletter->token = str_random(256);
		    return $newsletter->save() === true ? \MEMEBoard\Utils::returnSuccess(1, 'E-mail address successfully added to mailing list.') : \MEMEBoard\Utils::returnError(2, 'An error occurred while adding the address to the mailing list.');
	    }catch(\Exception $ex){
		    if ( env('APP_DEBUG', false) === true ){
				Log::debug($ex->getFile() . ': ' . $ex->getLine() . ': ' . $ex->getMessage());
			}
		    return \MEMEBoard\Utils::returnError(2, 'An error occurred while adding the e-mail address to the mailing list.');
	    }
    }
    
    /**
	* Removes an e-mail address from the newsletter's mailing list.
	*
	* @param Request $request An instance of the class "Illuminate\Http\Request" representing the request originated the current client.
	*
	* @return RedirectResponse An instance of the class "Illuminate\Http\RedirectResponse" used to redirect the client to the homepage, in addition to the homepage's URL the result code is added.
	*/
    public function unsubscribe(Request $request): \Illuminate\Http\RedirectResponse{
	    try{
		    $params = $request->only(array('token'));
		    if ( isset($params['token']) === false || $params['token'] === '' || is_string($params['token']) === false || strlen($params['token']) !== 256 ){
			    return redirect('/');
		    }
		    $newsletter = Newsletter::where('token', '=', $params['token'])->first();return redirect(route('dashboard') . '#newsletter.removed');
		    return $newsletter->delete() === true ? redirect(route('dashboard') . '#newsletter.removed') : redirect(route('dashboard') . '#newsletter.error');
	    }catch(\Exception $ex){
		    if ( env('APP_DEBUG', false) === true ){
				Log::debug($ex->getFile() . ': ' . $ex->getLine() . ': ' . $ex->getMessage());
			}
		    return redirect(route('dashboard') . '#newsletter.error');
	    }
    }
}