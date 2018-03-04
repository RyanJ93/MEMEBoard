<?php
namespace App\Http\Controllers;

require dirname(__FILE__) . '/../../../resources/library.php';

use Illuminate\Http\Request;

use App\Models\MEME;
use App\Models\Category;
use App\Models\Vote;
use App\Models\User;
use App\Models\Comment;
use App\Models\Newsletter;

use Storage;
use Auth;
use DB;
use Log;

class MEMEController extends Controller{
	/**
	* @const array RATIOS 
	*/
	const RATIOS = array(
		'1:1' => array(800, 800),
		'4:3' => array(800, 600),
		'16:9' => array(800, 450),
		'16:10' => array(800, 500)
	);
	
	/**
	* @const int MEMES_PER_PAGE An integer number representing the amount of MEMEs returned for each page.
	*/
	const MEMES_PER_PAGE = 12;
	
	/**
	* @const int SITEMAP_ENTRIES An integer number representing the amount of MEMEs returned for each page of the sitemap, max supported value according to standard is 50000, however consider to stay under 40000 allowing to the first page to include static pages, categories and users.
	*/
	const SITEMAP_ENTRIES = 40000;
	
	/**
	* Processes the file to be uploaded validating and resizing it.
	*
	* @param string $path A string containing the path ot the file.
	* @param string $extension A string containing the file extension.
	* @param string $ratio A string containing the aspect ratio that shall be applied to the given image or video.
	* @param string $dir An optional string containing the directory there the file shall be stored into.
	*
	* @return string A string containing the path to the processed file.
	*
	* @throws InvalidArgumentException If an empty file path is given.
	* @throws InvalidArgumentException If an empty file extension is given.
	* @throws Exception If an error occurs during the process.
	* @throws Exception If the function "shell_exec" is not enabled and a path to a video is given, this function is required to use "ffmpeg" to process videos.
	*/
	protected static function processFile(string $path, string $extension, string $ratio, string $dir = NULL): string{
		if ( $path === '' || $path === NULL ){
			throw new \InvalidArgumentException('Path cannot be empty.');
		}
		if ( $extension === '' || $extension === NULL ){
			throw new \InvalidArgumentException('Extension cannot be empty.');
		}
		set_time_limit(0);
		$ratioSizes = $ratio === '' || $ratio === NULL || isset(self::RATIOS[$ratio]) === false ? self::RATIOS['1:1'] : self::RATIOS[$ratio];
		if ( $extension === 'mp4' && function_exists('shell_exec') === false ){
			throw new \Exception('Cannot process videos without "shell_exec" enabled, please enable this function in order to allow to this script to use "ffmpeg".');
		}
		try{
			$fullPath = dirname(__FILE__) . '/../../../storage/app/public/' . $path;
			switch ( $extension ){
				case 'jpg':
				case 'jpeg':
				case 'png':{
					$path = mb_substr($path, 0, mb_strpos($path, '.')) . '.jpg';
					$image = new \Imagick($fullPath);
					$newPath = dirname(__FILE__) . '/../../../storage/app/public/' . $path;
					$dimensions = $image->getImageGeometry();
					if ( ( $dimensions['width'] / $dimensions['height'] ) > ( $ratioSizes[0] / $ratioSizes[1] ) ){
						$width = ( $dimensions['height'] * $ratioSizes[0] ) / $ratioSizes[1];
						$height = $dimensions['height'];
						$margin = ( $dimensions['width'] - $width ) / 2;
						$image->cropImage($width, $height, $margin, 0);
						$image->setImagePage($width, $height, 0, 0);
					}else{
						$width = $dimensions['width'];
						$height = ( $dimensions['width'] * $ratioSizes[1] ) / $ratioSizes[0];
						$margin = ( $dimensions['height'] - $height ) / 2;
						$image->cropImage($width, $height, 0, $margin);
						$image->setImagePage($width, $height, 0, 0);
					}
					$image->resizeImage($ratioSizes[0], $ratioSizes[1], \imagick::FILTER_LANCZOS, 0.9, false);
					$image->setImageFormat('jpg');
					$image->stripImage();
					$image->writeImage($newPath);
					@unlink($fullPath);
					return $path;
				}break;
				case 'gif':{
					$image = new \Imagick($fullPath);
					$image = $image->coalesceImages();
					$dimensions = $aspect = NULL;
					do{
						if ( $dimensions === NULL ){
							$dimensions = $image->getImageGeometry();
							$aspect = ( $dimensions['width'] / $dimensions['height'] ) > ( $ratioSizes[0] / $ratioSizes[1] ) ? true : false;
						}
						if ( $aspect === true ){
							$width = ( $dimensions['height'] * $ratioSizes[0] ) / $ratioSizes[1];
							$height = $dimensions['height'];
							$margin = ( $dimensions['width'] - $width ) / 2;
							$image->cropImage($width, $height, $margin, 0);
							$image->setImagePage($width, $height, 0, 0);
						}else{
							$width = $dimensions['width'];
							$height = ( $dimensions['width'] * $ratioSizes[1] ) / $ratioSizes[0];
							$margin = ( $dimensions['height'] - $height ) / 2;
							$image->cropImage($width, $height, 0, $margin);
							$image->setImagePage($width, $height, 0, 0);
						}
						$image->resizeImage($ratioSizes[0], $ratioSizes[1], \imagick::FILTER_LANCZOS, 0.9, false);
					}while( $image->nextImage() === true );
					$image = $image->deconstructImages();
					$image->writeImages($fullPath, true);
					return $path;
				}break;
				case 'mp4':{
					$_fullPath = escapeshellarg($fullPath);
					$data = shell_exec('ffprobe -v quiet -print_format json -show_format -show_streams ' . $_fullPath);
					if ( $data === false || $data === '' ){
						throw new \Exception('Invalid response from ffprobe.');
					}
					$data = json_decode($data, true);
					if ( $data === NULL || isset($data['streams']) === false || is_array($data['streams']) === false ){
						throw new \Exception('Invalid response from ffprobe.');
					}
					$dim = NULL;
					foreach ( $data['streams'] as $key => $value ){
						if ( isset($value['codec_type']) === false || $value['codec_type'] !== 'video' ){
							continue;
						}
						$value['width'] = isset($value['width']) === true && is_int($value['width']) === true && $value['width'] > 0 ? $value['width'] : NULL;
						$value['height'] = isset($value['height']) === true && is_int($value['height']) === true && $value['height'] > 0 ? $value['height'] : NULL;
						if ( $value['width'] === NULL || $value['height'] === NULL ){
							continue;
						}
						$dim = array(
							'width' => $value['width'],
							'height' => $value['height']
						);
					}
					if ( $dim === NULL ){
						throw new \Exception('Unable to get video size.');
					}
					$data = isset($data['format']['duration']) === true && is_string($data['format']['duration']) === true && $data['format']['duration'] !== '' ? floatval($data['format']['duration']) : NULL;
					if ( $data <= 0 || $data === NULL ){
						throw new \Exception('Unable to get video length.');
					}
					$max = intval(env('MAX_VIDEO_LENGTH', 180));
					if ( $max > 0 && $data > $max ){
						throw new \Exception('The length of your video is too much.');
					}
					if ( ( $dim['width'] / $dim['height'] ) > ( $ratioSizes[0] / $ratioSizes[1] ) ){
						$width = ( $dim['height'] * $ratioSizes[0] ) / $ratioSizes[1];
						$height = $dim['height'];
						$margin = ( $dim['width'] - $width ) / 2;
					}else{
						$width = $dim['width'];
						$height = ( $dim['width'] * $ratioSizes[1] ) / $ratioSizes[0];
						$margin = ( $dim['height'] - $height ) / 2;
					}
					$out = $dir . '/' . hash('md5', $path . microtime()) . '.mp4';
					$command = 'ffmpeg -i ' . $_fullPath;
					$command .= ' -vf "crop=' . escapeshellarg($width) . ':' . escapeshellarg($height) . ':' . escapeshellarg($margin);
					$command .= ',scale=' . escapeshellarg($ratioSizes[0]) . 'x' . escapeshellarg($ratioSizes[1]) . ',setdar=' . escapeshellarg($ratio) . '"';
					$command .= ' -r 24 -c:v libx264 -crf 22 -strict -2 -y ' . escapeshellarg(dirname(__FILE__) . '/../../../storage/app/public/' . $out);
					shell_exec($command);
					if ( file_exists($fullPath) === true ){
						unlink($fullPath);
					}
					return $out;
				}break;
			}
		}catch(\Exception $ex){
			if ( env('APP_DEBUG', false) === true ){
				Log::debug($ex->getFile() . ': ' . $ex->getLine() . ': ' . $ex->getMessage());
			}
			throw new \Exception('Unable to process the image.', NULL, $ex);
		}
	}
	
	/**
	* Gets MEMEs from the database according to the given mode and filter.
	*
	* @param int $mode An integer number greater than zero and lower or equal that 4 representing the reading mode (1 => all, 2 => by user, 3 => by category, 4 => search).
	* @param int $page An integer number greater or equal than one representing the page used in record pagination.
	* @param string $filter An optional string used to filter recornds, note that this filter will be ignored if mode is set to 1 (2 => an user ID, 3 => a category ID, 4 => a text that will searched).
	* @param string $ordering An optional string containing the filtering mode, note that currently this parameter will be considered only if the mode is set to one.
	*
	* @return array A sequential array containing the returned elements.
	*
	* @throws Exception If an error occurs during the process.
	* @throws InvalidArgumentException If the given filter is a number lower or equal than zero while filtering by user or category.
	* @throws InvalidArgumentException If the given filter is an empty string while using search.
	*/
	protected function getMEMEs(int $mode = NULL, int $page = 1, string $filter = NULL, string $ordering = NULL): array{
		if ( $mode === 2 || $mode === 3 ){
			$filter = intval($filter);
			if ( $filter <= 0 ){
				throw new \InvalidArgumentException('Filter ID cannot be lower or equal than zero.');
			}
		}elseif ( $mode === 3 && $filter === '' ){
			throw new \InvalidArgumentException('Filter cannot be an empty string.');
		}
		try{
			$timer = microtime(true);
			$page = ( $page - 1 ) * self::MEMES_PER_PAGE;
			if ( $mode > 1 && $mode <= 4 && ( $filter === NULL || $filter === '' ) ){
				return array();
			}
			switch ( $mode ){
				case 2:{
					$memes = MEME::with('creator')->with('categories')->with('categories')->where('user', '=', intval($filter));
				}break;
				case 3:{
					$memes = MEME::with('creator')->with('categories');
					$memes->whereHas('categories', function($meme) use ($filter){
						$meme->where('category_id', '=', $filter);
					});
				}break;
				case 4:{
					$memes = MEME::with('creator')->with('categories')->where('title', 'like', '%' . $filter . '%')->orWhere('text', 'like', '%' . $filter . '%');
				}break;
				default:{
					$memes = MEME::with('creator')->with('categories');
				}break;
			}
			if ( $ordering === 'new' ){
				$memes->orderBy('created_at', 'DESC');
			}else{
				$memes->orderBy('up_votes', 'DESC')->orderBy('comments', 'DESC')->orderBy('created_at', 'DESC');
			}
			if ( $mode === 4 ){
				$count = MEME::where('title', 'like', '%' . $filter . '%')->orWhere('text', 'like', '%' . $filter . '%')->count();
			}
			$memes = $memes->skip($page)->take(self::MEMES_PER_PAGE)->get();
			$user = Auth::check() === true ? Auth::user()->id : NULL;
		    $elements = array();
		    $i = 0;
		    foreach ( $memes as $key => $value ){
			    $elements[$i] = array(
				    'id' => $value->id,
				    'title' => $value->title,
				    'path' => $value->processedPath,
				    'type' => $value->type,
				    'ratio' => $value->ratio,
				    'counters' => array(
					    'upVotes' => $value->up_votes,
					    'downVotes' => $value->down_votes,
					    'comments' => $value->comments
				    ),
				    'user' => ( $value->user !== NULL && $value->creator !== NULL ? array(
					    'id' => $value->creator->id,
					    'name' => $value->creator->fullName
				    ) : NULL ),
				    'date' => $value->created_at->timestamp,
				    'categories' => $value->categories,
				    'vote' => array(
					    'positive' => false,
					    'negative' => false
				    )
			    );
			    foreach ( $elements[$i]['categories'] as $_key => $_value ){
				    $elements[$i]['categories'][$_key] = $_value->name;
			    }
			    if ( $user !== NULL ){
				    $vote = Vote::where('user', '=', $user)->where('meme', '=', $value->id)->first();
				    if ( $vote !== NULL ){
					    if ( $vote->positive === 1 ){
						    $elements[$i]['vote']['positive'] = true;
					    }else{
						    $elements[$i]['vote']['negative'] = true;
					    }
				    }
			    }
			    $elements[$i]['mine'] = $user !== NULL && $value->user !== NULL && $value->creator !== NULL && $user === $value->creator->id ? true : false;
			    $i++;
		    }
		    $timer = microtime(true) - $timer;
		    return $mode === 4 ? array(
			    'count' => $count,
			    'time' => $timer,
			    'elements' => $elements
		    ) : $elements;
		}catch(\Exception $ex){
			if ( env('APP_DEBUG', false) === true ){
				Log::debug($ex->getFile() . ': ' . $ex->getLine() . ': ' . $ex->getMessage());
			}
			throw new \Exception('An error occurred while fetching the records.', NULL, $ex);
		}
	}
	
	/**
	* Gets the MEMEs.
	*
	* @param Request $request An instance of the class "Illuminate\Http\Request" representing the request originated the current client.
	*
	* @return JsonResponse An isntance of the class "Illuminate\Http\Response" representing the response that will be sent to the client.
	*/
    public function index(Request $request): \Illuminate\Http\JsonResponse{
	    try{
		    $params = $request->only(array('page', 'mode', 'q', 'author', 'category', 'ordering'));
		    $params['page'] = isset($params['page']) === true ? intval($params['page']) : 1;
		    if ( $params['page'] <= 0 ){
			    $params['page'] = 1;
		    }
		    $params['ordering'] = isset($params['ordering']) === false || $params['ordering'] !== 'new' ? 'popular' : 'new';
		    if ( isset($params['mode']) === false || $params['mode'] === '' || is_string($params['mode']) === false ){
			    $params['mode'] = NULL;
		    }
		    $elements = array();
		    switch ( $params['mode'] ){
			    case 'search':{
				    if ( isset($params['q']) === true && $params['q'] !== '' && is_string($params['q']) === true ){
					    $elements = $this->getMEMEs(4, $params['page'], $params['q'], $params['ordering']);
				    }
			    }break;
			    case 'author':{
				    if ( isset($params['author']) === false || $params['author'] === '' || is_string($params['author']) === false ){
					    return \MEMEBoard\Utils::returnError(2, 'Invalid author ID.');
				    }
				    $params['author'] = intval($params['author']);
				    if ( $params['author'] <= 0 ){
					    return \MEMEBoard\Utils::returnError(2, 'Invalid author ID.');
				    }
				    $elements = $this->getMEMEs(2, $params['page'], strval($params['author']), $params['ordering']);
			    }break;
			    case 'category':{
				    if ( isset($params['category']) === false || $params['category'] === '' || is_string($params['category']) === false ){
					    return \MEMEBoard\Utils::returnError(3, 'Invalid category ID.');
				    }
				    $params['category'] = intval($params['category']);
				    if ( $params['category'] <= 0 ){
					    return \MEMEBoard\Utils::returnError(3, 'Invalid category ID.');
				    }
				    $elements = $this->getMEMEs(3, $params['page'], strval($params['category']), $params['ordering']);
			    }break;
			    default:{
				    $elements = $this->getMEMEs(1, $params['page'], NULL, $params['ordering']);
			    }break;
		    }
		    return \MEMEBoard\Utils::returnSuccess(1, 'Memes fetched successfully.', $elements);
	    }catch(\Exception $ex){
		    if ( env('APP_DEBUG', false) === true ){
				Log::debug($ex->getFile() . ': ' . $ex->getLine() . ': ' . $ex->getMessage());
			}
		    return \MEMEBoard\Utils::returnError(1, 'An error occurred while fetching MEMEs.');
	    }
    }
    
    /**
	* Shows the dashboard's view.
	*
	* @param Request $request An instance of the class "Illuminate\Http\Request" representing the request originated the current client.
	*
	* @return View An instance of the class "Illuminate\View\View" representing the view, if an error occurrs, an HTTP exception will be sent.
	*/
    public function showDashboard(Request $request){
	    $variables = array(
		    'role' => $request->path(),
		    'title' => env('BASE_TITLE', 'MEMEBoard')
	    );
	    $index = mb_strpos($variables['role'], '/');
	    if ( $index !== false ){
		    $variables['role'] = mb_substr($variables['role'], 0, $index);
	    }
	    switch ( $variables['role'] ){
		    case '':{
			    $variables['role'] = 'dashboard';
		    }break;
		    case 'search':{
				$variables['title'] .= ' | Search for MEMEs'; 
				$variables['q'] = isset($_GET['q']) === true && $_GET['q'] !== '' && is_string($_GET['q']) === true ? $_GET['q'] : '';
		    }break;
		    case 'author':{
			    try{
				    $user = User::find($request->route('user'));
				    if ( $user === NULL ){
					    return abort(404);
				    }
				    $variables['title'] .= ' | MEMEs by ' . ( $user->name === '' || $user->surname === '' ? ( $user->name . $user->surname ) : ( $user->name . ' ' . $user->surname ) );
				    $variables['author'] = $user->id;
				    $variables['fullName'] = $user->fullName;
				    $variables['counter'] = \MEMEBoard\Utils::stringifyCounterValue(MEME::where('user', '=', $user->id)->count());
			    }catch(\Exception $ex){
				    if ( env('APP_DEBUG', false) === true ){
						Log::debug($ex->getFile() . ': ' . $ex->getLine() . ': ' . $ex->getMessage());
					}
				    return abort(404);
			    }
		    }break;
		    case 'category':{
			    try{
				    $category = Category::where('name', '=', $request->route('category'))->first();
				    if ( $category === NULL ){
					    return abort(404);
				    }
				    $counter = MEME::with('creator')->with('categories');
					$counter->whereHas('categories', function($counter) use ($category){
						$counter->where('category_id', '=', $category->id);
					});
					$counter = $counter->count();
				    $variables['title'] .= ' | ' . $category->name;
				    $variables['category'] = $category->id;
				    $variables['categoryName'] = $category->name;
				    $variables['counter'] = \MEMEBoard\Utils::stringifyCounterValue($counter);
			    }catch(\Exception $ex){
				    if ( env('APP_DEBUG', false) === true ){
						Log::debug($ex->getFile() . ': ' . $ex->getLine() . ': ' . $ex->getMessage());
					}
				    return abort(404);
			    }
		    }break;
		    default:{
			    return abort(404);
		    }break;
	    }
	    return view('dashboard', $variables);
    }
	
	/**
	* Shows the view used to create a new MEME.
	*
	* @return View An instance of the class "Illuminate\View\View" representing the view, if no user is logged in or if the user is not an admin, the connection will be redirected to the homepage.
	*/
    public function create(){
	   try{
		    if ( Auth::check() === false || Auth::user()->admin !== 1 ){
			    return redirect('/');
		    }
		    $categories = Category::orderBy('name', 1)->get();
	        return view('create', array(
		        'categories' => $categories
	        ));
	   }catch(\Exception $ex){
		   if ( env('APP_DEBUG', false) === true ){
				Log::debug($ex->getFile() . ': ' . $ex->getLine() . ': ' . $ex->getMessage());
			}
		   return redirect('/');
	   }
    }
	
	/**
	* Creates a new MEME by using the parameters sent within the requet.
	*
	* @param Request $request An instance of the class "Illuminate\Http\Request" representing the request originated the current client.
	*
	* @return JsonResponse An isntance of the class "Illuminate\Http\Response" representing the response that will be sent to the client.
	*/
    public function store(Request $request): \Illuminate\Http\JsonResponse{
        try{
	        if ( Auth::check() === false ){
		        return \MEMEBoard\Utils::returnUnauthorizedError(false);
	        }
	        $user = Auth::user();
	        if ( $user->admin !== 1 ){
		        return \MEMEBoard\Utils::returnUnauthorizedError(true);
	        }
	        $data = $request->only(array('title', 'category', 'text', 'new_category', 'ratio'));
	        if ( isset($data['title']) === false || $data['title'] === '' || is_string($data['title']) === false ){
		        return \MEMEBoard\Utils::returnError(3, 'You must provide a title.');
	        }
	        if ( mb_strlen($data['title'], 'UTF-8') > 30 ){
		        return \MEMEBoard\Utils::returnError(4, 'The inserted title is too long.');
	        }
	        $data['ratio'] = isset($data['ratio']) === false || $data['ratio'] === '' || is_string($data['ratio']) === false || isset(self::RATIOS[$data['ratio']]) === false ? '1:1' : $data['ratio'];
	        $categories = isset($data['category']) === false || $data['category'] === '' || is_array($data['category']) === false ? array() : $data['category'];
	        $newCategories = isset($data['new_category']) === false || $data['new_category'] === '' || is_array($data['new_category']) === false ? array() : $data['new_category'];
	        $categories = Category::prepareCategories($categories, $newCategories);
	        $categoriesCount = count($categories);
	        if ( $categoriesCount === 0 ){
		        return \MEMEBoard\Utils::returnError(5, 'You must select at least one category.');
	        }
	        if ( $categoriesCount > 3 ){
		        return \MEMEBoard\Utils::returnError(6, 'You can select up to 3 categories.');
	        }
	        if ( isset($data['text']) === false || is_string($data['text']) === false ){
		        $data['text'] = '';
	        }
	        if ( mb_strlen($data['text']) > 1000 ){
		        return \MEMEBoard\Utils::returnError(8, 'The given text is too long.');
	        }
	        if ( $request->hasFile('file') === false ){
		        return \MEMEBoard\Utils::returnError(1, 'You must provide a file.');
	        }
	        $dir = env('MEME_PATH', NULL);
	        if ( $dir === '' || $dir === NULL ){
		        return \MEMEBoard\Utils::returnError(7, 'An error occurred while processing the MEME.');
	        }
	        $dir = $dir . '/' . $user->id;
	        $storage = Storage::drive('public');
			if ( $storage->exists($dir) === false ){
				$storage->makeDirectory($dir);
			}
			$file = $request->file('file');
			$extension = mb_strtolower($file->extension(), 'UTF-8');
			if ( $file->isValid() === false || in_array($extension, array('jpg', 'jpeg', 'png', 'gif', 'mp4')) === false ){
				return \MEMEBoard\Utils::returnError(2, 'Unsupported file type.');
			}
			$file = $file->store($dir, 'public');
			if ( is_string($file) === false ){
				return \MEMEBoard\Utils::returnError(7, 'An error occurred while processing the MEME.');
			}
			$file = self::processFile($file, $extension, $data['ratio'], $dir);
			$meme = new MEME();
			$meme->title = $data['title'];
			$meme->text = $data['text'];
			$meme->path = substr($file, strrpos($file, '/') + 1);
			$meme->ratio = $data['ratio'];
			if ( $extension === 'gif' ){
				$meme->type = 2;
			}elseif ( $extension === 'mp4' ){
				$meme->type = 3;
			}else{
				$meme->type = 1;
			}
			$meme->creator()->associate($user);
			if ( $meme->save() === true ){
				$meme->categories()->sync($categories);
				Newsletter::send($meme);
				return \MEMEBoard\Utils::returnSuccess(1, 'Meme created successfully.', array(
					'id' => $meme->id
				));
			}
			if ( isset($file) === true ){
		        Storage::drive('public')->delete($file);
	        }
			return \MEMEBoard\Utils::returnError(7, 'An error occurred while processing the MEME.');
        }catch(\Exception $ex){
	        if ( isset($file) === true ){
		        Storage::drive('public')->delete($file);
	        }
	        if ( env('APP_DEBUG', false) === true ){
				Log::debug($ex->getFile() . ': ' . $ex->getLine() . ': ' . $ex->getMessage());
			}
	        return \MEMEBoard\Utils::returnError(7, 'An error occurred while processing the MEME.');
        }
    }
	
	/**
	* Shows a given MEME.
	*
	* @param MEME $meme An instance of the class "App\Models\MEME" representing the MEME.
	* @param Request $request An instance of the class "Illuminate\Http\Request" representing the request originated the current client.
	*
	* @return View An instance of the class "Illuminate\View\View" representing the view.
	*/
    public function show(MEME $meme, Request $request){
	    try{
		    $user = '';
		    $identifier = $request->ip();
		    if ( Auth::check() === true ){
			    $user = Auth::user();
				$identifier = $user->id;
			    $user = $user->name === '' || $user->surname === '' ? ( $user->name . $user->surname ) : ( $user->name . ' ' . $user->surname );
		    }
		    $meme->incrementViewsCounter($identifier);
		    if ( $meme->user === NULL || $meme->creator === NULL ){
			    $creator = $creatorID = NULL;
		    }else{
			    $creator = $meme->creator->name === '' || $meme->creator->surname === '' ? ( $meme->creator->name . $meme->creator->surname ) : ( $meme->creator->name . ' ' . $meme->creator->surname );
			    $creatorID = $meme->creator->id;
		    }
		    $votes = array(
			    'positive' => false,
			    'negative' => false
		    );
		    if ( $user !== '' ){
			    $vote = Vote::where('user', '=', $identifier)->where('meme', '=', $meme->id)->first();
			    if ( $vote !== NULL ){
				    if ( $vote->positive === 1 ){
					    $votes['positive'] = true;
				    }else{
					    $votes['negative'] = true;
				    }
			    }
		    }
		    $metaImage = '';
		    if ( $meme->type !== 3 ){
			    $metaImage = $meme->relativePath === true ? ( env('APP_URL', '') . $meme->processedPath ) : $meme->processedPath;
		    }
	        return view('meme', array(
		        'meme' => $meme,
		        'user' => $user,
		        'counters' => array(
				    'up_votes' => \MEMEBoard\Utils::stringifyCounterValue($meme->up_votes),
				    'down_votes' => \MEMEBoard\Utils::stringifyCounterValue($meme->down_votes),
				    'comments' => \MEMEBoard\Utils::stringifyCounterValue($meme->comments),
				    'views' => \MEMEBoard\Utils::stringifyCounterValue($meme->views)
			    ),
			    'votes' => $votes,
			    'metaImage' => $metaImage,
			    'creator' => $creator,
			    'creatorID' => $creatorID
	        ));
	    }catch(\Exception $ex){
		    if ( env('APP_DEBUG', false) === true ){
				Log::debug($ex->getFile() . ': ' . $ex->getLine() . ': ' . $ex->getMessage());
			}
		    return redirect('/');
	    }
    }
	
	/**
	* Removes a given MEME.
	*
	* @param MEME $meme An instance of the class "App\Models\MEME" representing the MEME.
	*
	* @return JsonResponse An isntance of the class "Illuminate\Http\Response" representing the response that will be sent to the client.
	*/
    public function destroy(MEME $meme): \Illuminate\Http\JsonResponse{
        try{
	        if ( Auth::check() === false ){
		        return \MEMEBoard\Utils::returnUnauthorizedError(false);
	        }
	        $user = Auth::user();
	        if ( $user->admin !== 1 ){
		        if ( $meme->user !== $user->id ){
			        return \MEMEBoard\Utils::returnError(1, 'You cannot remove a MEME that is not yours.');
		        }
	        }
	        if ( $meme->relativePath === true ){
		        Storage::drive('public')->delete($meme->processedPath);
	        }
	        DB::table('views')->where('meme', '=', $meme->id)->delete();
	        if ( $meme->delete() === true ){
		    	return \MEMEBoard\Utils::returnSuccess(1, 'Meme removed successfully.');
	        }
	        return \MEMEBoard\Utils::returnError(2, 'An error occurred while removing the MEME.');
        }catch(\Exception $ex){
	        if ( env('APP_DEBUG', false) === true ){
				Log::debug($ex->getFile() . ': ' . $ex->getLine() . ': ' . $ex->getMessage());
			}
	        return \MEMEBoard\Utils::returnError(2, 'An error occurred while removing the MEME.');
        }
    }
    
    /**
	* Toggles a positive or negative vote on a given MEME.
	*
	* @param MEME $meme An instance of the class "App\Models\MEME" representing the MEME.
	* @param Request $request An instance of the class "Illuminate\Http\Request" representing the request originated the current client.
	*
	* @return JsonResponse An isntance of the class "Illuminate\Http\Response" representing the response that will be sent to the client.
	*/
    public function toggleVote(MEME $meme, Request $request): \Illuminate\Http\JsonResponse{
	    try{
		    if ( Auth::check() === false ){
			    return \MEMEBoard\Utils::returnUnauthorizedError(false);
		    }
		    $params = $request->only(array('value'));
		    if ( $meme === NULL ){
			    return \MEMEBoard\Utils::returnError(1, 'Invalid MEME.');
		    }
		    $params['value'] = isset($params['value']) === true && $params['value'] === '0' ? false : true;
		    $user = Auth::user();
		    $result = $meme->toggleVote($params['value'], $user);
		    return \MEMEBoard\Utils::returnSuccess(1, 'Meme voted successfully.', array(
			    'positive' => $result === true && $params['value'] === true ? true : false,
				'negative' => $result === true && $params['value'] === false ? true : false
		    ));
	    }catch(\Exception $ex){
		    if ( env('APP_DEBUG', false) === true ){
				Log::debug($ex->getFile() . ': ' . $ex->getLine() . ': ' . $ex->getMessage());
			}
	        return \MEMEBoard\Utils::returnError(1, 'An error occurred while voting the element.');
        }
    }
    
	/**
	* Generates the sitemap content as XML string and then sends it to the client.
	*
	* @param Request $request An instance of the class "Illuminate\Http\Request" representing the request originated the current client.
	*
	* @return Response An isntance of the class "Illuminate\Http\Response" representing the response that will be sent to the client.
	*/
    public function getSitemap(Request $request): \Illuminate\Http\Response{
	    try{
		    $page = $request->only(array('page'));
		    $page = isset($page['page']) === true && $page['page'] !== '' && is_string($page['page']) === true ? intval($page['page']) : NULL;
		    if ( $page !== NULL && $page <= 0 ){
			    $page = 1;
		    }
		    $domain = env('APP_URL', '') . '/';
		    $ret = '<?xml version="1.0" encoding="UTF-8"?>';
		    if ( $page === NULL ){
			    $count = MEME::count();
			    $count = floor($count / self::SITEMAP_ENTRIES) + 1;
			    $ret .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
			    for ( $i = 0 ; $i < $count ; $i++ ){
				    $ret .= '<sitemap><loc>' . $domain . 'sitemap.xml?page=' . ( $i + 1 ) . '</loc></sitemap>';
			    }
			    return response($ret . '</sitemapindex>', 200)->header('Content-Type', 'application/xml');
		    }
		    $ret .= '<urlset>';
		    if ( $page === 1 ){
			    $ret .= '<url><loc>' . $domain . '</loc><changefreq>daily</changefreq><priority>1</priority></url>';
			    $ret .= '<url><loc>' . $domain . 'memes</loc><changefreq>daily</changefreq><priority>0.9</priority></url>';
			    $ret .= '<url><loc>' . $domain . 'about</loc><changefreq>monthly</changefreq><priority>0.9</priority></url>';
			    $ret .= '<url><loc>' . $domain . 'search</loc><changefreq>never</changefreq><priority>0.9</priority></url>';
			    foreach ( Category::get() as $key => $value ){
				    $ret .= '<url><loc>' . $domain . 'category/' . urlencode($value->name) . '</loc><changefreq>daily</changefreq><priority>0.75</priority></url>';
			    }
			    foreach ( User::where('admin', '=', '1')->get() as $key => $value ){
				    $ret .= '<url><loc>' . $domain . 'user/' . $value->id . '</loc><changefreq>daily</changefreq><priority>0.75</priority></url>';
			    }
		    }
		    foreach ( MEME::orderBy('created_at', 'DESC')->skip($page - 1)->take(self::SITEMAP_ENTRIES)->get() as $key => $value ){
			    $ret .= '<url><loc>' . $domain . 'meme/' . $value->id . '</loc><changefreq>never</changefreq><priority>0.75</priority></url>';
		    }
		    return response($ret . '</urlset>', 200)->header('Content-Type', 'application/xml');
	    }catch(\Exception $ex){
		    if ( env('APP_DEBUG', false) === true ){
				Log::debug($ex->getFile() . ': ' . $ex->getLine() . ': ' . $ex->getMessage());
			}
			return response('Internal server error', 503);
	    }
    }
    
    /**
	* Generates the RSS feed content as XML string and then sends it to the client.
	*
	* @return Response An isntance of the class "Illuminate\Http\Response" representing the response that will be sent to the client.
	*/
    public function getRSSFeed(): \Illuminate\Http\Response{
	    try{
		    $domain = env('APP_URL', '') . '/';
		    $ret = '<?xml version="1.0"?><rss version="2.0"><channel>';
		    $buffer = env('RSS_TITLE');
		    $buffer = is_string($buffer) === true ? $buffer : '';
		    $ret .= '<title>' . $buffer . '</title><link>' . $domain . '</link>';
		    $buffer = env('RSS_DESCRIPTION');
		    $buffer = is_string($buffer) === true ? $buffer : '';
		    $ret .= '<description>' . $buffer . '</description></channel>';
		    foreach ( MEME::with('creator')->orderBy('created_at', 'DESC')->where('created_at', '>',	 date('Y-m-01'))->get() as $key => $value ){
			    $date = $value->created_at;
			    $date->setTimezone(new \DateTimeZone('UTC'));
			    $ret .= '<item><title>' . htmlentities($value->title, \ENT_XML1) . '</title>';
			    $ret .= '<link>' . $domain . 'meme/' . $value->id . '</link>';
			    $ret .= '<pubDate>' . $date->format(\DateTime::RSS) . '</pubDate>';
			    $ret .= '<description>' . htmlentities($value->text, \ENT_XML1) . '</description>';
			    $ret .= '<guid>' . $value->id . '</guid>';
			    if ( $value->user !== NULL && $value->creator !== NULL ){
				    $ret .= '<author>' . htmlentities($value->creator->fullName, \ENT_XML1) . '</author>';
			    }
			    $ret .= '</item>';
		    }
		    return response($ret . '</channel></rss>', 200)->header('Content-Type', 'application/rss+xml');
	    }catch(\Exception $ex){
		    if ( env('APP_DEBUG', false) === true ){
				Log::debug($ex->getFile() . ': ' . $ex->getLine() . ': ' . $ex->getMessage());
			}
			return response('Internal server error', 503);
	    }
    }
}