<?php
namespace App\Http\Controllers;

require dirname(__FILE__) . '/../../../resources/library.php';

use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Comment;
use App\Models\Vote;
use App\Models\Newsletter;
use App\Models\Category;
use App\Models\MEME;

use DB;
use Auth;
use Log;

class CategoryController extends Controller{
	/**
	* @const int CATEGORIES_PER_PAGE An integer number representing the amount of categories returned for each page.
	*/
	const CATEGORIES_PER_PAGE = 12;
	
	/**
	* @const int TRENDS_LIMIT An integer number representing the amount of trend categories returned for each page.
	*/
	const TRENDS_LIMIT = 10;
	
	/**
	* Returns the trend categories.
	*
	* @return JsonResponse An isntance of the class "Illuminate\Http\Response" representing the response that will be sent to the client.
	*/
    public function getTrends(): \Illuminate\Http\JsonResponse{
	    try{
		    $elements = DB::select('SELECT COUNT(id) AS num, name FROM ( SELECT categories.id AS id, categories.name AS name FROM categories INNER JOIN category_memes ON categories.id = category_memes.category_id INNER JOIN memes ON category_memes.meme_id = memes.id ORDER BY memes.created_at DESC ) AS _table GROUP BY id ORDER BY num DESC LIMIT ' . intval(self::TRENDS_LIMIT) . ';');
		    $ret = array();
		    foreach ( $elements as $key => $value ){
			    $ret[] = array(
				    'count' => $value->num,
				    'name' => $value->name
			    );
		    }
		    return \MEMEBoard\Utils::returnSuccess(1, 'Trends fetched successfully.', $ret);
	    }catch(\Exception $ex){
		    if ( env('APP_DEBUG', false) === true ){
				Log::debug($ex->getFile() . ': ' . $ex->getLine() . ': ' . $ex->getMessage());
			}
		    return \MEMEBoard\Utils::returnError(1, 'An error occurred while fetching trends.');
	    }
    }
    
    /**
	* Returns the view that contains the list of all categories.
	*
	* @return View An instance of the class "Illuminate\View\View" representing the view.
	*/
    public function index(){
        return view('dashboard', array(
	        'role' => 'categories',
	        'title' => 'Categories'
        ));
    }
    
    /**
	* Returns the categories.
	*
	* @param Request $request An instance of the class "Illuminate\Http\Request" representing the request originated the current client.
	*
	* @return JsonResponse An isntance of the class "Illuminate\Http\Response" representing the response that will be sent to the client.
	*/
    public function getCategories(Request $request): \Illuminate\Http\JsonResponse{
	    try{
		    $params = $request->only(array('page'));
		    $params['page'] = isset($params['page']) === true ? intval($params['page']) : 1;
		    if ( $params['page'] <= 0 ){
			    $params['page'] = 1;
		    }
		    $params['page'] = ( $params['page'] - 1 ) * self::CATEGORIES_PER_PAGE;
		    $elements = DB::table('categories');
		    $elements->selectRaw('COUNT(category_memes.meme_id) AS num, categories.name AS name, categories.id AS id');
		    $elements->leftJoin('category_memes', 'categories.id', '=', 'category_memes.category_id');
		    $elements->groupBy('categories.name');
		    $elements->orderBy('num', 'DESC');
		    $elements->take(self::CATEGORIES_PER_PAGE);
		    $elements->skip($params['page']);
		    $elements = $elements->get();
		    $user = Auth::check() === true ? Auth::user()->id : NULL;
		    $ret = array();
		    $i = 0;
		    foreach ( $elements as $key => $value ){
			    $meme = MEME::whereHas('categories', function($element) use ($value){
				    $element->where('category_id', '=', $value->id);
			    })->inRandomOrder()->first();
			    $ret[$i] = array(
				    'meme' => NULL,
				    'id' => $value->id,
					'name' => $value->name,
					'count' => $value->num
			    );
			    if ( $meme === NULL ){
				    $i++;
				    continue;
			    }
				$ret[$i]['meme'] = array(
				    'id' => $meme->id,
				    'title' => $meme->title,
				    'path' => $meme->path,
				    'type' => $meme->type,
				    'ratio' => $meme->ratio
				);
			    $i++;
		    }
		    return \MEMEBoard\Utils::returnSuccess(1, 'Memes fetched successfully', $ret);
	    }catch(\Exception $ex){
		    if ( env('APP_DEBUG', false) === true ){
				Log::debug($ex->getFile() . ': ' . $ex->getLine() . ': ' . $ex->getMessage());
			}
		    return \MEMEBoard\Utils::returnError(1, 'An error occurred while fetching categories.');
	    }
    }
	
	/**
	* Removes a given category.
	*
	* @param Category An instance of the class "App\Models\Category" representing the category that shall be removed.
	*
	* @return JsonResponse An isntance of the class "Illuminate\Http\Response" representing the response that will be sent to the client.
	*/
    public function destroy(Category $category): \Illuminate\Http\JsonResponse{
        try{
	        if ( Auth::check() === false ){
			    return \MEMEBoard\Utils::returnUnauthorizedError(false);
		    }
		    if ( Auth::user()->admin !== 1 ){
			    return \MEMEBoard\Utils::returnUnauthorizedError(true);
		    }
	        $elements = MEME::whereHas('categories', function($element){
		    	$element->where('category_id', '=', $category->id);
	        })->exists();
	        if ( $elements !== false ){
		        return \MEMEBoard\Utils::returnError(3, 'Non-empty categories remotion is not supported yet.');
	        }
	        return $category->delete();
        }catch(\Exception $ex){
	        if ( env('APP_DEBUG', false) === true ){
				Log::debug($ex->getFile() . ': ' . $ex->getLine() . ': ' . $ex->getMessage());
			}
		    return \MEMEBoard\Utils::returnError(1, 'An error occurred while removing the category.');
	    }
    }
}