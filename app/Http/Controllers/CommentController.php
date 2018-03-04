<?php
namespace App\Http\Controllers;

require dirname(__FILE__) . '/../../../resources/library.php';

use Illuminate\Http\Request;

use App\Models\MEME;
use App\Models\Category;
use App\Models\Vote;
use App\Models\User;
use App\Models\Comment;

use Auth;

class CommentController extends Controller{
	/**
	* @const int COMMENTS_PER_PAGE An integer number representing the amount of comments returned for each page.
	*/
    const COMMENTS_PER_PAGE = 20;
    
    /**
	* Returns all the comment for a given MEME.
	*
	* @param Request $request An instance of the class "Illuminate\Http\Request" representing the request originated the current client.
	*
	* @return JsonResponse An isntance of the class "Illuminate\Http\Response" representing the response that will be sent to the client.
	*/
    public function index(Request $request): \Illuminate\Http\JsonResponse{
        try{
	        $params = $request->only(array('meme', 'page'));
	        if ( isset($params['meme']) === false || $params['meme'] === '' || is_string($params['meme']) === false ){
		        return \MEMEBoard\Utils::returnError(1, 'No valid meme ID specified.');
	        }
	        $params['meme'] = intval($params['meme']);
	        if ( $params['meme'] <= 0 ){
		        return \MEMEBoard\Utils::returnError(1, 'No valid meme ID specified.');
	        }
	        $params['page'] = isset($params['page']) === true ? intval($params['page']) : 1;
	        if ( $params['page'] <= 0 ){
		        $params['page'] = 1;
	        }
	        $params['page'] = ( $params['page'] - 1 ) * self::COMMENTS_PER_PAGE;
	        $user = Auth::check() === true ? Auth::user() : NULL;
	        $elements = array();
	        $i = 0;
	        foreach ( Comment::with('creator')->where('meme', '=', $params['meme'])->skip($params['page'])->take(self::COMMENTS_PER_PAGE)->orderBy('created_at', 1)->get() as $key => $value ){
		        $elements[$i] = array(
			        'id' => $value->id,
			        'text' => $value->text,
			        'date' => $value->created_at->timestamp
		        );
		        $elements[$i]['mine'] = false;
		        if ( $value->user !== NULL && $value->creator !== NULL ){
			        $elements[$i]['user'] = array(
				        'id' => $value->creator->id,
				        'name' => ( $value->creator->name === '' || $value->creator->surname === '' ? ( $value->creator->name . $value->creator->surname ) : ( $value->creator->name . ' ' . $value->creator->surname ) )
			        );
			        if ( $user !== NULL && $elements[$i]['user']['id'] === $user ){
				         $elements[$i]['mine'] = true;
			        }
		        }
		        $i++;
	        }
	        return \MEMEBoard\Utils::returnSuccess(1, 'Comments fetched successfully.', $elements);
        }catch(\Exception $ex){
	        if ( env('APP_DEBUG', false) === true ){
				Log::debug($ex->getFile() . ': ' . $ex->getLine() . ': ' . $ex->getMessage());
			}
	        return \MEMEBoard\Utils::returnError(2, 'An error occurred while fetching the comments.');
        }
    }
    
    /**
	* Returns all the comment for a given user.
	*
	* @param User $user An instance of the class "App\Models\User" that represents the user.
	* @param Request $request An instance of the class "Illuminate\Http\Request" representing the request originated the current client.
	*
	* @return JsonResponse An isntance of the class "Illuminate\Http\Response" representing the response that will be sent to the client.
	*/
    public function getUserComments(User $user, Request $request): \Illuminate\Http\JsonResponse{
	    try{
	        $params = $request->only(array('page'));
	        $params['page'] = isset($params['page']) === true ? intval($params['page']) : 1;
	        if ( $params['page'] <= 0 ){
		        $params['page'] = 1;
	        }
	        $params['page'] = ( $params['page'] - 1 ) * self::COMMENTS_PER_PAGE;
	        $mine = Auth::check() === true && Auth::user()->id === $user->id ? true : false;
	        $fullName = $user->name === '' || $user->surname === '' ? ( $user->name . $user->surname ) : ( $user->name . ' ' . $user->surname );
	        $elements = array();
	        $i = 0;
	        foreach ( Comment::where('user', '=', $user->id)->skip($params['page'])->take(self::COMMENTS_PER_PAGE)->orderBy('created_at', 1)->get() as $key => $value ){
		        $elements[$i] = array(
			        'id' => $value->id,
			        'text' => $value->text,
			        'date' => $value->created_at->timestamp
		        );
		        $elements[$i]['mine'] = $mine;
		        $elements[$i]['user'] = array(
			        'id' => $user->id,
			        'name' => $fullName
		        );
		        $i++;
	        }
	        return \MEMEBoard\Utils::returnSuccess(1, 'Comments fetched successfully.', $elements);
        }catch(\Exception $ex){
	        if ( env('APP_DEBUG', false) === true ){
				Log::debug($ex->getFile() . ': ' . $ex->getLine() . ': ' . $ex->getMessage());
			}
	        return \MEMEBoard\Utils::returnError(2, 'An error occurred while fetching the comments.');
        }
    }
	
	/**
	* Creates a new comemnt.
	*
	* @param Request $request An instance of the class "Illuminate\Http\Request" representing the request originated the current client.
	*
	* @return JsonResponse An isntance of the class "Illuminate\Http\Response" representing the response that will be sent to the client.
	*/
    public function store(Request $request): \Illuminate\Http\JsonResponse{
        try{
	        $params = $request->only(array('meme', 'text'));
	        if ( isset($params['meme']) === false || $params['meme'] === '' || is_string($params['meme']) === false ){
		        return \MEMEBoard\Utils::returnError(1, 'Invalid meme ID provided.');
	        }
	        $params['meme'] = intval($params['meme']);
	        if ( $params['meme'] <= 0 ){
		        return \MEMEBoard\Utils::returnError(1, 'Invalid meme ID provided.');
	        }
	        if ( isset($params['text']) === false || $params['text'] === '' || is_string($params['text']) === false ){
		        return \MEMEBoard\Utils::returnError(2, 'An empty text has been provided.');
	        }
	        $params['text'] = trim($params['text']);
	        if ( $params['text'] === '' ){
		        return \MEMEBoard\Utils::returnError(1, 'An empty text has been provided.');
	        }
	        if ( mb_strlen($params['text'], 'UTF-8') > 10000 ){
		        return \MEMEBoard\Utils::returnError(3, 'The provided text is too long.');
	        }
	        $params['meme'] = MEME::find($params['meme']);
	        if ( $params['meme'] === NULL ){
		        return \MEMEBoard\Utils::returnError(4, 'No such meme found matching the given ID.');
	        }
	        $user = Auth::check() === true ? Auth::user() : NULL;
	        $comment = new Comment();
	        $comment->text = $params['text'];
	        $comment->meme = $params['meme']->id;
	        if ( $user === NULL ){
		        $comment->user = NULL;
	        }else{
		        $comment->creator()->associate($user);
	        }
	        if ( $comment->save() === false ){
		        return \MEMEBoard\Utils::returnError(5, 'Unable to create the comment.');
	        }
	        $element = array(
		        'id' => $comment->id,
				'text' => $comment->text,
				'date' => $comment->created_at->timestamp
	        );
	        if ( $user !== NULL ){
		        $element['user'] = array(
			        'id' => $user->id,
				    'name' => ( $user->name === '' || $user->surname === '' ? ( $user->name . $user->surname ) : ( $user->name . ' ' . $user->surname ) )
		        );
	        }
	        $element['mine'] = $user === NULL ? false : true;
	        $params['meme']->comments = $params['meme']->comments + 1;
	        return $params['meme']->save() === true ? \MEMEBoard\Utils::returnSuccess(1, 'The comment has successfully been created.', $element) : \MEMEBoard\Utils::returnError(6, 'An error occurred while creating the comment.');
        }catch(\Exception $ex){
	        if ( env('APP_DEBUG', false) === true ){
				Log::debug($ex->getFile() . ': ' . $ex->getLine() . ': ' . $ex->getMessage());
			}
	        return \MEMEBoard\Utils::returnError(6, 'An error occurred while creating the comment.');
        }
    }
	
	/**
	* Removes a comemnt
	*
	* @param Comment $comment An instance of the class "App\Models\Comment" representing the comment that shall be removed.
	*
	* @return JsonResponse An isntance of the class "Illuminate\Http\Response" representing the response that will be sent to the client.
	*/
    public function destroy(Comment $comment): \Illuminate\Http\JsonResponse{
        try{
	        if ( Auth::check() === false ){
		        return \MEMEBoard\Utils::returnUnauthorizedError(false);
	        }
	        $user = Auth::user();
	        if ( $user->admin !== 1 ){
		        if ( $comment->user === NULL || $comment->user->id !== $user->id ){
			    	return \MEMEBoard\Utils::returnUnauthorizedError(true);
		        }
	        }
	        $meme = $comment->element;
	        if ( $comment->delete() === true ){
		    	$meme->comments = $meme->comments - 1;
		    	if ( $meme->comments < 0 ){
			    	$meme->comments = 0;
		    	}
		    	if ( $meme->save() === true ){
			    	return \MEMEBoard\Utils::returnSuccess(1, 'The comment has successfully been removed.');
		    	}
	        }
	        return \MEMEBoard\Utils::returnError(1, 'Unable to remove the comment.');
        }catch(\Exception $ex){
	        if ( env('APP_DEBUG', false) === true ){
				Log::debug($ex->getFile() . ': ' . $ex->getLine() . ': ' . $ex->getMessage());
			}
	        return \MEMEBoard\Utils::returnError(1, 'Unable to remove the comment.');
        }
    }
}