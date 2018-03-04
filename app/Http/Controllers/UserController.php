<?php
namespace App\Http\Controllers;

require dirname(__FILE__) . '/../../../resources/library.php';

use Illuminate\Http\Request;

use App\Models\MEME;
use App\Models\Category;
use App\Models\Vote;
use App\Models\User;
use App\Models\Newsletter;
use App\Models\Comment;

use Auth;
use Hash;
use Mail;
use DB;
use Log;

use PHPEmailAddressValidator\PHPEmailAddressValidator;
use PHPPasswordToolBox\Analyzer;

class UserController extends Controller{
	/**
	* @const int USERS_PER_PAGE An integer number representing the amount of users returned for each page.
	*/
	const USERS_PER_PAGE = 20;
	
	/**
	* Sends a mail to a given e-mail address using defult template.
	*
	* @param string $email A string containing the e-mail address.
	* @param string $fullName A string containing the full name of the user to whom the message will be sent.
	* @param string $title A string containing the message title.
	* @param string $text A string containing the message.
	*
	* @throws InvalidArgumentException If an invalid e-mail address is given.
	* @throws InvalidArgumentException If an empty message is given.
	* @throws Exception If an error occurrs while sending the e-mail message.
	*/
	protected static function sendEmail(string $email, string $fullName, string $title, string $text){
		if ( $email === '' || $email === NULL || filter_var($email, \FILTER_VALIDATE_EMAIL) === false ){
			throw new \InvalidArgumentException('Invalid e-mail address.');
		}
		if ( $text === '' || $text === NULL ){
			throw new \InvalidArgumentException('Message cannot be empty.');
		}
		try{
			$fullName = $fullName === NULL ? '' : $fullName;
			$title = $title === NULL ? '' : $title;
			Mail::send('mail/generic', array(
			    'title' => $title,
			    'text' => $text
		    ), function($message) use ($email, $fullName, $title){
			    $message->to($email, $fullName)->subject($title);
		    });
		}catch(\Exception $ex){
			if ( env('APP_DEBUG', false) === true ){
				Log::debug($ex->getFile() . ': ' . $ex->getLine() . ': ' . $ex->getMessage());
			}
			throw new \Exception('An error occurred while sending the message.', NULL, $ex);
		}
	}
	
	/**
	* Validates a given e-mail address and check if it is existing and if is disposable.
	*
	* @param string $address A string containing the e-mail address.
	*
	* @return array An associative array containing two indexes, "existing" (representing if the address exists) and "trusted" (representing if the address is in white list, black list or if is a disposable one).
	*
	* @throws InvalidArgumentException If an invalid e-mail address is given.
	* @throws Exception If an error occurred during the analysis.
	*/
	protected static function validateEmailAddress(string $address): array{
		if ( $address === '' || $address === NULL || filter_var($address, \FILTER_VALIDATE_EMAIL) === false ){
			throw new \InvalidArgumentException('Invalid e-mail address.');
		}
		$data = array(
			'existing' => true,
			'trusted' => true
		);
		try{
			$check = false;
		    $dir = '/../../../';
		    $list = env('USER_EMAIL_VALIDATION_WHITELIST', '');
		    if ( $list !== '' && file_exists($dir . $list) === true ){
			    PHPEmailAddressValidator::setWhiteListDatabasePath($dir . $list);
			    $check = true;
		    }
		    $list = env('USER_EMAIL_VALIDATION_BLACKLIST', '');
		    if ( $list !== '' && file_exists($dir . $list) === true ){
			    PHPEmailAddressValidator::setWhiteListDatabasePath($dir . $list);
			    $check = true;
		    }
		    $list = env('USER_EMAIL_VALIDATION_DISPOSABLE_PROVIDERS', '');
		    if ( $list !== '' && file_exists($dir . $list) === true ){
			    PHPEmailAddressValidator::setWhiteListDatabasePath($dir . $list);
			    $check = true;
		    }
		    $data['existing'] = PHPEmailAddressValidator::validate($address);
		    if ( $check === true ){
			    $disposable = env('USER_EMAIL_VALIDATION_ALLOW_DISPOSABLE', true) === false ? false : true;
			    $data['trusted'] = PHPEmailAddressValidator::isTrustedProvider($address, $disposable, false);
		    }
		    return $data;
		}catch(\Exception $ex){
			if ( env('APP_DEBUG', false) === true ){
				Log::debug($ex->getFile() . ': ' . $ex->getLine() . ': ' . $ex->getMessage());
			}
			return $data;
		}
	}
	
	/**
	* Authenticates the user.
	*
	* @param Request $request An instance of the class "Illuminate\Http\Request" representing the request originated the current client.
	*
	* @return JsonResponse An isntance of the class "Illuminate\Http\Response" representing the response that will be sent to the client.
	*/
    public function login(Request $request): \Illuminate\Http\JsonResponse{
	    try{
		    $params = $request->only(array('email', 'password', 'remember'));
		    if ( isset($params['email']) === false || $params['email'] === '' || is_string($params['email']) === false || filter_var($params['email'], \FILTER_VALIDATE_EMAIL) === false ){
			    return \MEMEBoard\Utils::returnError(1, 'Invalid e-mail address.');
		    }
		    if ( isset($params['password']) === false || $params['password'] === '' || is_string($params['password']) === false || mb_strlen($params['password'], 'UTF-8') > 30 ){
			    return \MEMEBoard\Utils::returnError(2, 'Invalid password.');
		    }
		    $remember = isset($params['remember']) === true && $params['remember'] === '1' ? true : false;
		    return Auth::attempt(array(
			    'email' => $params['email'],
			    'password' => $params['password']
		    ), $remember) === true ? \MEMEBoard\Utils::returnSuccess(1, 'User authenticated successfully.') : \MEMEBoard\Utils::returnError(3, 'Invalid credentials.');
	    }catch(\Exception $ex){
		    if ( env('APP_DEBUG', false) === true ){
				Log::debug($ex->getFile() . ': ' . $ex->getLine() . ': ' . $ex->getMessage());
			}
		    return \MEMEBoard\Utils::returnError(4, 'An error occurred while authenticating the user.');
	    }
    }
    
    /**
	* Creates a new user by using the information passed within the request.
	*
	* @param Request $request An instance of the class "Illuminate\Http\Request" representing the request originated the current client.
	*
	* @return JsonResponse An isntance of the class "Illuminate\Http\Response" representing the response that will be sent to the client.
	*/
    public function register(Request $request): \Illuminate\Http\JsonResponse{
	    try{
		     $params = $request->only(array('name', 'surname', 'email', 'password'));
		    if ( isset($params['name']) === false || $params['name'] === '' || is_string($params['name']) === false || mb_strlen($params['name'], 'UTF-8') > 30 ){
			    return \MEMEBoard\Utils::returnError(1, 'Invalid name.');
		    }
		    if ( isset($params['surname']) === false || $params['surname'] === '' || is_string($params['surname']) === false || mb_strlen($params['surname'], 'UTF-8') > 30 ){
			    return \MEMEBoard\Utils::returnError(1, 'Invalid surname.');
		    }
		    if ( isset($params['email']) === false || $params['email'] === '' || is_string($params['email']) === false || filter_var($params['email'], \FILTER_VALIDATE_EMAIL) === false ){
			    return \MEMEBoard\Utils::returnError(3, 'Invalid e-mail address.');
		    }
		    if ( isset($params['password']) === false || $params['password'] === '' || is_string($params['password']) === false || mb_strlen($params['password'], 'UTF-8') > 30 ){
			    return \MEMEBoard\Utils::returnError(4, 'Invalid password.');
		    }
		    $user = User::where('email', '=', $params['email'])->count();
		    if ( $user > 0 ){
			    return \MEMEBoard\Utils::returnError(5, 'User already existing.');
		    }
		    $result = self::validateEmailAddress($params['email']);
		    if ( $result['existing'] === false ){
			    return \MEMEBoard\Utils::returnError(7, 'The given e-mail address appears to be invalid or not existing.');
		    }
		    if ( $result['trusted'] === false ){
			    return \MEMEBoard\Utils::returnError(8, 'The given e-mail address is disposable or not accepted.');
		    }
		    $user = new User();
		    $user->name = $params['name'];
		    $user->surname = $params['surname'];
		    $user->email = $params['email'];
		    $user->password = Hash::make($params['password']);
		    $user->admin = false;
		    if ( $user->save() === false ){
			    return \MEMEBoard\Utils::returnError(6, 'An error occurred while creating the user.');
		    }
		    $newsletter = new Newsletter();
		    $newsletter->email = $params['email'];
		    $newsletter->token = str_random(256);
		    $newsletter->save();
		    $app = htmlentities(env('APP_NAME', 'MEMEBoard'), \ENT_HTML5);
		    $title = 'Welcome to ' . $app;
		    $text = 'Welcome to ' . $app . ' ' . htmlentities($user->fullName, \ENT_HTML5) . '! Your account has successfully been created and activated, you can login to your profile using your credentials.';
		    self::sendEmail($user->email, $user->fullName, $title, $text);
		    return Auth::loginUsingId($user->id) ? \MEMEBoard\Utils::returnSuccess(1, 'User created successfully.') : \MEMEBoard\Utils::returnError(6, 'An error occurred while creating the user.');
	    }catch(\Exception $ex){
		    if ( env('APP_DEBUG', false) === true ){
				Log::debug($ex->getFile() . ': ' . $ex->getLine() . ': ' . $ex->getMessage());
			}
		    return \MEMEBoard\Utils::returnError(6, 'An error occurred while creating the user.');
	    }
    }
    
    /**
	* Sends an e-mail message to the user containing the link used for password recovery.
	*
	* @param Request $request An instance of the class "Illuminate\Http\Request" representing the request originated the current client.
	*
	* @return JsonResponse An isntance of the class "Illuminate\Http\Response" representing the response that will be sent to the client.
	*/
    public function sendPasswordRestoreRequest(Request $request): \Illuminate\Http\JsonResponse{
	    try{
		    $params = $request->only(array('email'));
		    if ( isset($params['email']) === false || $params['email'] === '' || is_string($params['email']) === false || filter_var($params['email'], \FILTER_VALIDATE_EMAIL) === false ){
				return \MEMEBoard\Utils::returnError(1, 'Invalid e-mail address.');
		    }
		    $user = User::where('email', '=', $params['email'])->first();
		    if ( $user === NULL ){
			    return \MEMEBoard\Utils::returnError(2, 'User not found.');
		    }
		    $token = str_random(255);
		    DB::table('password_resets')->insert(array(
			    'email' => $user->email,
			    'token' => $token
		    ));
		    $app = htmlentities(env('APP_NAME', 'MEMEBoard'), \ENT_HTML5);
		    $title = $app . ' | Password reset.';
		    $text = env('APP_URL', '') . '/passwordRestore?token=' . urlencode($token) . '&email=' . urlencode($user->email);
		    $text = 'You received this e-mail message because a password restore request has been issued. To set a new password please use this <a id="main-content-link" target="_blank" href="' . $text . '">link</a>.';
		    self::sendEmail($user->email, $user->fullName, $title, $text);
		    return \MEMEBoard\Utils::returnSuccess(1, 'Request sent successfully.');
	    }catch(\Exception $ex){
		    if ( env('APP_DEBUG', false) === true ){
				Log::debug($ex->getFile() . ': ' . $ex->getLine() . ': ' . $ex->getMessage());
			}
		    return \MEMEBoard\Utils::returnError(3, 'An error occurred while sending password restore request.');
	    }
    }
    
    /**
	* Shows the view used to recover the password, if it's an invalid request, the client will be redirected to the homepage.
	*
	* @param Request $request An instance of the class "Illuminate\Http\Request" representing the request originated the current client.
	*
	* @return View An instance of the class "Illuminate\View\View" representing the view.
	*/
    public function showPasswordRestoreView(Request $request){
	    if ( Auth::check() === true ){
		    return redirect('/');
	    }
	    $params = $request->only(array('token', 'email'));
	    if ( isset($params['token']) === false || $params['token'] === '' || is_string($params['token']) === false || strlen($params['token']) !== 255 ){
		    return redirect('/');
	    }
	    if ( isset($params['email']) === false || $params['email'] === '' || is_string($params['email']) === false || filter_var($params['email'], \FILTER_VALIDATE_EMAIL) === false ){
		    return redirect('/');
	    }
	    return view('passwordRestore', array(
		    'token' => $params['token'],
		    'email' => $params['email']
	    ));
    }
    
    /**
	* Changes the user password by using a token previously sent through a password recovery request.
	*
	* @param Request $request An instance of the class "Illuminate\Http\Request" representing the request originated the current client.
	*
	* @return JsonResponse An isntance of the class "Illuminate\Http\Response" representing the response that will be sent to the client.
	*/
    public function restorePassword(Request $request): \Illuminate\Http\JsonResponse{
	    try{
		    if ( Auth::check() === true ){
			    return \MEMEBoard\Utils::returnError(8, 'An user is already logged in.');
		    }
		    $params = $request->only(array('token', 'email', 'password'));
		    if ( isset($params['token']) === false || $params['token'] === '' || is_string($params['token']) === false || strlen($params['token']) !== 255 ){
			    return \MEMEBoard\Utils::returnError(2, 'Invalid token.');
		    }
		    if ( isset($params['email']) === false || $params['email'] === '' || is_string($params['email']) === false || filter_var($params['email'], \FILTER_VALIDATE_EMAIL) === false ){
			    return \MEMEBoard\Utils::returnError(3, 'Invalid e-mail address.');
		    }
		    if ( isset($params['password']) === false || $params['password'] === '' || is_string($params['password']) === false ){
			    return \MEMEBoard\Utils::returnError(4, 'Invalid password.');
		    }
		    if ( mb_strlen($params['password']) > 30 ){
			    return \MEMEBoard\Utils::returnError(5, 'The provided password is too long.');
		    }
		    if ( DB::table('password_resets')->where('token', '=', $params['token'])->where('email', '=', $params['email'])->count() !== 1 ){
			    return \MEMEBoard\Utils::returnError(6, 'Token mismatch.');
		    }
		    $user = User::where('email', '=', $params['email'])->first();
		    if ( $user === NULL ){
			    return \MEMEBoard\Utils::returnError(7, 'User not found.');
		    }
		    $user->password = Hash::make($params['password']);
		    if ( $user->save() === true ){
			    DB::table('password_resets')->where('email', '=', $params['email'])->delete();
			    if ( Auth::loginUsingId($user->id) ){
				    $app = htmlentities(env('APP_NAME', 'MEMEBoard'), \ENT_HTML5);
				    $title = $app . ' | Password changed.';
				    $text = 'Your password has recently been changed. <br />If was not you, this means that probably your account security has been compromised, in this case, please contact us as soon as possible.';
				    self::sendEmail($user->email, $user->fullName, $title, $text);
				    return \MEMEBoard\Utils::returnSuccess(1, 'Password updated successfully.');
			    }
		    }
		    return \MEMEBoard\Utils::returnError(1, 'An error occurred while setting the new password.');
	    }catch(\Exception $ex){
		    if ( env('APP_DEBUG', false) === true ){
				Log::debug($ex->getFile() . ': ' . $ex->getLine() . ': ' . $ex->getMessage());
			}
		    return \MEMEBoard\Utils::returnError(1, 'An error occurred while setting the new password.');
	    }
    }
    
    /**
	* Removes the session of the authenticated user.
	*
	* @return JsonResponse An isntance of the class "Illuminate\Http\Response" representing the response that will be sent to the client.
	*/
    public function logout(): \Illuminate\Http\JsonResponse{
	    try{
		    Auth::logout();
		    return \MEMEBoard\Utils::returnSuccess(1, 'User logged out successfully.');
	    }catch(\Exception $ex){echo $ex->getMessage();
		    return \MEMEBoard\Utils::returnError(1, 'Unable to log out the user.');
	    }
    }
    
    /**
	* Returns the view that lists all registered users, note that this page is accessible only to admins.
	*
	* @return View An instance of the class "Illuminate\View\View" representing the view, if no user is authenticated or if the user is not an admin, the client will be redirected to the homepage.
	*/
    public function index(){
	    if ( Auth::check() === false || Auth::user()->admin !== 1 ){
		    return redirect('/');
	    }
	    return view('users');
    }
    
    /**
	* Shows the user profile.
	*
	* @param User $user An instance of the class "App\Models\User" representing the user, if set to NULL, the authenticated used will be used instead, if no authenticated user is found, the client will be redirected to the homepage.
	*
	* @return View An instance of the class "Illuminate\View\View" representing the view, if an error occurrs while getting user data, the client will be redirected to the homepage.
	*/
    public function profile(User $user = NULL){
	    try{
		    if ( $user === NULL ){
			    if ( Auth::check() === false ){
				    redirect(route('dashboard'));
			    }
			    $user = Auth::user();
			    $current = $user->id;
		    }else{
			    $current = Auth::check() === true ? Auth::user()->id : NULL;
		    }
		    $comments = Comment::where('user', '=', $user->id)->count();
		    $memes = $user->admin === 1 ? MEME::where('user', '=', $user->id)->count() : NULL;
		    return view('user', array(
			    'user' => $user,
			    'fullName' => ( $user->name === '' || $user->surname === '' ? ( $user->name . $user->surname ) : ( $user->name . ' ' . $user->surname ) ),
			    'counters' => array(
				    'comments' => \MEMEBoard\Utils::stringifyCounterValue($comments),
				    'memes' => ( $memes === NULL ? NULL : \MEMEBoard\Utils::stringifyCounterValue($memes) )
			    ),
			    'mine' => ( $current !== NULL && $user->id === $current ? true : false ),
			    'admin' => ( $current !== NULL ? ( Auth::user()->admin === 1 ? true : false ) : false )
		    ));
	    }catch(\Exception $ex){
		    if ( env('APP_DEBUG', false) === true ){
				Log::debug($ex->getFile() . ': ' . $ex->getLine() . ': ' . $ex->getMessage());
			}
		    return redirect(route('dashboard'));
	    }
    }
    
    /**
	* Shows the view used to edit user data.
	*
	* @param User $user An instance of the class "App\Models\User" representing the user to edit (if the authenticated user is an admin) or current user.
	*
	* @return View An instance of the class "Illuminate\View\View" representing the view, if the user has not the permit to access to this page, the client will be redirected to the user profile.
	*/
    public function edit(User $user){
	    try{
		    if ( Auth::check() === false || ( Auth::user()->id !== $user->id && Auth::user()->admin !== 1 ) ){
			    return redirect(route('user.profile', $user->id));
		    }
		    return view('user_edit', array(
			    'user' => $user,
			    'fullName' => ( $user->name === '' || $user->surname === '' ? ( $user->name . $user->surname ) : ( $user->name . ' ' . $user->surname ) )
		    ));
	    }catch(\Exception $ex){
		    if ( env('APP_DEBUG', false) === true ){
				Log::debug($ex->getFile() . ': ' . $ex->getLine() . ': ' . $ex->getMessage());
			}
		    return redirect(route('user.profile', $user->id));
	    }
    }
    
    /**
	* Updates user information.
	*
	* @param User $user An instance of the class "App\Models\User" representing the user to edit (if the authenticated user is an admin) or current user.
	* @param Request $request An instance of the class "Illuminate\Http\Request" representing the request originated the current client.
	*
	* @return JsonResponse An isntance of the class "Illuminate\Http\Response" representing the response that will be sent to the client.
	*/
    public function update(User $user, Request $request): \Illuminate\Http\JsonResponse{
	    try{
		    if ( Auth::check() === false ){
			    return \MEMEBoard\Utils::returnUnauthorizedError(false);
		    }
		    if ( Auth::user()->id !== $user->id && Auth::user()->admin !== 1 ){
			    return \MEMEBoard\Utils::returnSuccess(8, 'You cannot edit this user.');
		    }
		    $params = $request->only(array('mode'));
		    switch ( ( isset($params['mode']) === false || is_string($params['mode']) === false ? NULL : $params['mode'] ) ){
			    case '1':{
				    $params = $request->only(array('name', 'surname', 'email'));
				    if ( isset($params['name']) === false || $params['name'] === '' || is_string($params['name']) === false ){
					    return \MEMEBoard\Utils::returnError(1, 'Invalid name.');
				    }
				    if ( mb_strlen($params['name'], 'UTF-8') > 30 ){
					    return \MEMEBoard\Utils::returnError(2, 'The given name is too long.');
				    }
				    if ( isset($params['surname']) === false || $params['surname'] === '' || is_string($params['surname']) === false ){
					    return \MEMEBoard\Utils::returnError(3, 'Invalid surname.');
				    }
				    if ( mb_strlen($params['surname'], 'UTF-8') > 30 ){
					    return \MEMEBoard\Utils::returnError(4, 'The given surname is too long.');
				    }
				    if ( isset($params['email']) === false || $params['email'] === '' || is_string($params['email']) === false || filter_var($params['email'], \FILTER_VALIDATE_EMAIL) === false ){
					    return \MEMEBoard\Utils::returnError(5, 'Invalid e-mail address.');
				    }
				    $user->name = $params['name'];
				    $user->surname = $params['surname'];
				    $user->email = $params['email'];
				    if ( $user->save() === true ){
					    return \MEMEBoard\Utils::returnSuccess(1, 'User edited successfully.');
				    }
				    return \MEMEBoard\Utils::returnError(7, 'An error occurred while editing the user.');
			    }break;
			    case '2':{
				    $params = $request->only(array('old_password', 'password'));
				    if ( isset($params['old_password']) === false || $params['old_password'] === '' || is_string($params['old_password']) === false ){
					    return \MEMEBoard\Utils::returnError(8, 'Current password is not valid.');
				    }
				    if ( mb_strlen($params['old_password'], 'UTF-8') > 30 ){
					    return \MEMEBoard\Utils::returnError(9, 'Current password is too long.');
				    }
				    if ( isset($params['password']) === false || $params['password'] === '' || is_string($params['password']) === false ){
					    return \MEMEBoard\Utils::returnError(10, 'New password is not valid.');
				    }
				    if ( mb_strlen($params['password'], 'UTF-8') > 30 ){
					    return \MEMEBoard\Utils::returnError(11, 'New password is too long.');
				    }
				    if ( Hash::check($params['old_password'], $user->password) === false ){
					    return \MEMEBoard\Utils::returnError(12, 'Current password is not correct.');
				    }
				    $user->password = Hash::make($params['password']);
				    if ( $user->save() === true ){
					    $app = htmlentities(env('APP_NAME', 'MEMEBoard'), \ENT_HTML5);
						$title = $app . ' | Password changed.';
						$text = 'Your password has recently been changed. <br />If was not you, this means that probably your account security has been compromised, in this case, please contact us as soon as possible.';
						self::sendEmail($user->email, $user->fullName, $title, $text);
					    return \MEMEBoard\Utils::returnSuccess(2, 'Password changed successfully.');
				    }
				    return \MEMEBoard\Utils::returnError(7, 'An error occurred while editing the user.');
			    }break;
			    default:{
				    return \MEMEBoard\Utils::returnError(6, 'Invalid mode.');
			    }break;
		    }
	    }catch(\Exception $ex){
		    if ( env('APP_DEBUG', false) === true ){
				Log::debug($ex->getFile() . ': ' . $ex->getLine() . ': ' . $ex->getMessage());
			}
		    return \MEMEBoard\Utils::returnError(7, 'An error occurred while editing the user.');
	    }
    }
    
    /**
	* Returns all registered users.
	*
	* @param Request $request An instance of the class "Illuminate\Http\Request" representing the request originated the current client.
	*
	* @return JsonResponse An isntance of the class "Illuminate\Http\Response" representing the response that will be sent to the client.
	*/
    public function getUsers(Request $request): \Illuminate\Http\JsonResponse{
	    try{
		    if ( Auth::check() === false ){
			    return \MEMEBoard\Utils::returnUnauthorizedError(false);
		    }
		    $id = Auth::user();
		    if ( $id->admin !== 1 ){
			    return \MEMEBoard\Utils::returnUnauthorizedError(true);
		    }
		    $id = $id->id;
		    $page = $request->only(array('page'));
		    $page = isset($page['page']) === true ? intval($page['page']) : 1;
		    if ( $page <= 0 ){
			    $page = 1;
		    }
		    $page = ( $page - 1 ) * self::USERS_PER_PAGE;
		    $users = User::orderBy('created_at', 'DESC')->skip($page)->take(self::USERS_PER_PAGE)->get();
		    $elements = array();
		    $i = 0;
		    foreach ( $users as $key => $value ){
			    $elements[$i] = array(
				    'id' => $value->id,
				    'name' => $value->name,
				    'surname' => $value->surname,
				    'email' => $value->email,
				    'admin' => $value->admin === 1 ? true : false,
				    'date' => $value->created_at->timestamp,
				    'me' => $value->id === $id ? true : false
			    );
			    $i++;
		    }
		    return \MEMEBoard\Utils::returnSuccess(1, 'Users fetched successfully.', $elements);
	    }catch(\Exception $ex){
		    if ( env('APP_DEBUG', false) === true ){
				Log::debug($ex->getFile() . ': ' . $ex->getLine() . ': ' . $ex->getMessage());
			}
		    return \MEMEBoard\Utils::returnError(1, 'An error occurred while fetching users.');
	    }
    }
    
    /**
	* Removes a given user.
	*
	* @param User $user An instance of the class "App\Models\User" representing the user to edit (if the authenticated user is an admin) or current user.
	* @param Request $request An instance of the class "Illuminate\Http\Request" representing the request originated the current client.
	*
	* @return JsonResponse An isntance of the class "Illuminate\Http\Response" representing the response that will be sent to the client.
	*/
    public function remove(User $user, Request $request): \Illuminate\Http\JsonResponse{
	    try{
		    if ( Auth::check() === false ){
				return \MEMEBoard\Utils::returnUnauthorizedError(false);
		    }
		    $auth = Auth::user();
		    if ( $user->id !== $auth->id && $auth->admin !== 1 ){
			    return \MEMEBoard\Utils::returnUnauthorizedError(true);
		    }
		    if ( $user->id === $auth->id && $auth->admin === 1 ){
			    if ( User::where('admin', '=', '1')->where('id', '!=', $auth->id)->exists() === false ){
				    return \MEMEBoard\Utils::returnError(1, 'Cannot remove this user, there must be at least one admin user.');
			    }
		    }
		    $params = $request->only(array('dropContents'));
		    //UPDATE memes SET memes.up_votes = memes.up_votes - 1 INNER JOIN votes ON memes.id = votes.meme WHERE votes.user = ? AND votes.positive = TRUE;
		    DB::table('memes')->join('votes', 'memes.id', '=', 'votes.meme')->where('votes.user', '=', $user->id)->where('votes.positive', '=', '1')->decrement('memes.up_votes', 1);
		    DB::table('memes')->join('votes', 'memes.id', '=', 'votes.meme')->where('votes.user', '=', $user->id)->where('votes.positive', '=', '0')->decrement('memes.down_votes', 1);
		    if ( $user->id === $auth->id || isset($params['dropContents']) === true && $params['dropContents'] === '1' ){
			    $user->removeContents();
		    }else{
			    DB::table('memes')->where('user', '=', $user->id)->update(array(
				    'user' => NULL
			    ));
				DB::table('comments')->where('user', '=', $user->id)->update(array(
				    'user' => NULL
			    ));
		    }
		    $id = $user->id;
		    if ( $user->delete() === true ){
			    return \MEMEBoard\Utils::returnSuccess(1, 'User removed successfully.');
		    }
		    return \MEMEBoard\Utils::returnError(2, 'An error occurred while removing the user.');
	    }catch(\Exception $ex){
		    if ( env('APP_DEBUG', false) === true ){
				Log::debug($ex->getFile() . ': ' . $ex->getLine() . ': ' . $ex->getMessage());
			}
		    return \MEMEBoard\Utils::returnError(2, 'An error occurred while removing the user.');
	    }
    }
    
    /**
	* Sets the admin rights for a given user.
	*
	* @param User $user An instance of the class "App\Models\User" representing the user to edit (if the authenticated user is an admin) or current user.
	* @param Request $request An instance of the class "Illuminate\Http\Request" representing the request originated the current client.
	*
	* @return JsonResponse An isntance of the class "Illuminate\Http\Response" representing the response that will be sent to the client.
	*/
    public function setAdminRights(User $user, Request $request): \Illuminate\Http\JsonResponse{
	    try{
		    if ( Auth::check() === false ){
			    return \MEMEBoard\Utils::returnUnauthorizedError(false);
		    }
		    $id = Auth::user();
		    if ( $id->admin !== 1 ){
			    return \MEMEBoard\Utils::returnUnauthorizedError(true);
		    }
		    $params = $request->only(array('value'));
		    $params['value'] = isset($params['value']) === true && $params['value'] === '1' ? true : false;
		    $user->setAdminRights($params['value']);
		    return \MEMEBoard\Utils::returnSuccess(1, 'User rights changed successfully.');
	    }catch(\Exception $ex){
		    if ( env('APP_DEBUG', false) === true ){
				Log::debug($ex->getFile() . ': ' . $ex->getLine() . ': ' . $ex->getMessage());
			}
		    return \MEMEBoard\Utils::returnError(2, 'An error occurred while changing user rights.');
	    }
    }
    
    /**
	* Validates a given e-mail address and check if it is existing and if is disposable.
	*
	* @param Request $request An instance of the class "Illuminate\Http\Request" representing the request originated the current client.
	*
	* @return JsonResponse An isntance of the class "Illuminate\Http\Response" representing the response that will be sent to the client.
	*/
    public function validateEmail(Request $request): \Illuminate\Http\JsonResponse{
	    try{
		    $params = $request->only(array('email'));
		    if ( isset($params['email']) === false || $params['email'] === '' || is_string($params['email']) === false || filter_var($params['email'], \FILTER_VALIDATE_EMAIL) === false ){
			    return \MEMEBoard\Utils::returnError(1, 'Invalid email address.');
		    }
		    $data = self::validateEmailAddress($params['email']);
		    return \MEMEBoard\Utils::returnSuccess(1, 'E-mail address checked.', $data);
	    }catch(\Exception $ex){
		    if ( env('APP_DEBUG', false) === true ){
				Log::debug($ex->getFile() . ': ' . $ex->getLine() . ': ' . $ex->getMessage());
			}
		    return \MEMEBoard\Utils::returnError(2, 'An error occurred while checking the e-mail address.');
	    }
    }
    
    /**
	* Analyzes a given password in order to test its strength.
	*
	* @param Request $request An instance of the class "Illuminate\Http\Request" representing the request originated the current client.
	*
	* @return JsonResponse An isntance of the class "Illuminate\Http\Response" representing the response that will be sent to the client.
	*/
    public function validatePassword(Request $request): \Illuminate\Http\JsonResponse{
	    try{
		    $params = $request->only(array('password', 'keywords'));
		    if ( isset($params['password']) === false || $params['password'] === '' || is_string($params['password']) === false ){
			    return \MEMEBoard\Utils::returnError(1, 'Invalid password.');
		    }
		    $params['keywords'] = isset($params['keywords']) === true && is_array($params['keywords']) === true ? array_values($params['keywords']) : NULL;
		    $analyzer = new Analyzer();
		    $dir = dirname(__FILE__) . '/../../../';
		    $list = env('USER_PASSWORD_VALIDATION_DICTIONARY', '');
		    if ( $list !== '' && file_exists($dir . $list) === true ){
			    $analyzer->setDictionaryPath($dir . $list);
		    }
		    return \MEMEBoard\Utils::returnSuccess(1, 'Password tested.', array(
			    'score' => $analyzer->setCaseInsensitive(true)->completeAnalysis($params['password'], $params['keywords'])['score']
		    ));
	    }catch(\Exception $ex){
		    if ( env('APP_DEBUG', false) === true ){
				Log::debug($ex->getFile() . ': ' . $ex->getLine() . ': ' . $ex->getMessage());
			}
		    return \MEMEBoard\Utils::returnError(2, 'An error occurred while testing the password.');
	    }
    }
}