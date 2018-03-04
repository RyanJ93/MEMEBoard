<?php
use Illuminate\Http\Request;
	
Route::get('/', 'MEMEController@showDashboard')->name('dashboard');
Route::get('/search', 'MEMEController@showDashboard')->name('dashboard.search');
Route::get('/author/{user}', 'MEMEController@showDashboard')->where('user', '[0-9]+')->name('dashboard.author');
Route::get('/category/{category}', 'MEMEController@showDashboard')->name('dashboard.category');
Route::get('/about', function(){
	$user = array(
		'name' => '',
		'email' => ''
	);
	if ( Auth::check() === true ){
		$userObject = Auth::user();
		$user = array(
			'name' => $userObject->fullName,
			'email' => $userObject->email
		);
	}
    return view('about')->with('user', $user);
})->name('about');
Route::post('/contact', function(Request $request){
	$params = $request->only(array('name', 'email', 'message'));
	if ( isset($params['name']) === false || $params['name'] === '' || is_string($params['name']) === false ){
		return array('result' => 'error', 'code' => 1, 'description' => 'Invalid name.');
	}
	if ( mb_strlen($params['name']) > 30 ){
		return array('result' => 'error', 'code' => 2, 'description' => 'Name too long.');
	}
	if ( isset($params['email']) === false || $params['email'] === '' || is_string($params['email']) === false || filter_var($params['email'], \FILTER_VALIDATE_EMAIL) === false ){
		return array('result' => 'error', 'code' => 3, 'description' => 'Invalid e-mail address.');
	}
	if ( isset($params['message']) === false || $params['message'] === '' || is_string($params['message']) === false ){
		return array('result' => 'error', 'code' => 4, 'description' => 'Invalid message.');
	}
	if ( mb_strlen($params['message']) > 10000 ){
		return array('result' => 'error', 'code' => 5, 'description' => 'Message too long.');
	}
	$title = 'Message from ' . htmlentities(env('APP_NAME'), \ENT_HTML5);
	$text = 'You have been contacted by ' . htmlentities($params['name'], \ENT_HTML5) . ' <' . htmlentities($params['email'], \ENT_HTML5) . '>, here you are the message: <br />' . htmlentities($params['message'], \ENT_HTML5);
	$email = env('MAIL_ADMIN', '');
	if ( $email !== '' && filter_var($email, \FILTER_VALIDATE_EMAIL) !== false ){
		Mail::send('mail/generic', array(
		    'title' => $title,
		    'text' => $text
	    ), function($message) use ($email, $title){
		    $message->to($email)->subject($title);
	    });
	}
	return array('result' => 'success', 'code' => 1, 'description' => 'Message sent successfully.');
})->name('contact');

/* USER */

Route::post('/login', 'UserController@login')->name('user.login');
Route::post('/register', 'UserController@register')->name('user.register');
Route::post('/logout', 'UserController@logout')->name('user.logout');
Route::get('/users', 'UserController@index')->name('user.show');
Route::get('/users/list', 'UserController@getUsers')->name('user.list');
Route::delete('/users/{user}', 'UserController@remove')->where('user', '[0-9]+')->name('user.delete');
Route::get('/users/{user}/setAdminRights', 'UserController@setAdminRights')->name('user.setAdminRights');
Route::get('/profile', 'UserController@profile')->name('user.currentProfile');
Route::get('/user', 'UserController@profile');
Route::get('/user/{user}', 'UserController@profile')->where('user', '[0-9]+')->name('user.profile');
Route::get('/user/{user}/edit', 'UserController@edit')->where('user', '[0-9]+')->name('user.edit');
Route::patch('/user/{user}/edit', 'UserController@update')->where('user', '[0-9]+')->name('user.update');
Route::get('/user/{user}/comments', 'CommentController@getUserComments')->where('user', '[0-9]+')->name('user.getUserComments');
Route::post('/user/sendPasswordRestoreRequest', 'UserController@sendPasswordRestoreRequest')->name('user.sendPasswordRestoreRequest');
Route::get('/passwordRestore', 'UserController@showPasswordRestoreView')->name('user.showPasswordRestoreView');
Route::patch('/restorePassword', 'UserController@restorePassword')->name('user.restorePassword');
Route::post('/validateEmail', 'UserController@validateEmail')->name('user.validateEmail');
Route::post('/validatePassword', 'UserController@validatePassword')->name('user.validatePassword');

/* MEMES */

Route::resource('memes', 'MEMEController');
Route::get('/create', 'MEMEController@create');
Route::post('/toggleVote/{meme}', 'MEMEController@toggleVote')->where('meme', '[0-9]+')->name('meme.toggleVote');
Route::get('/sitemap.xml', 'MEMEController@getSitemap')->name('sitemap');
if ( env('RSS_ENABLED', false) === true ){
	Route::get('/feed.rss', 'MEMEController@getRSSFeed')->name('feedRSS');
}

/* COMMENTS */

Route::resource('comments', 'CommentController');

/* CATEGORIES */

Route::get('/trends', 'CategoryController@getTrends')->name('category.trends');
Route::get('/categories', 'CategoryController@index')->name('category.index');
Route::get('/categories/list', 'CategoryController@getCategories')->name('category.list');
Route::delete('/category/{category}', 'CategoryController@destroy')->name('category.delete');

/* NEWSLETTER */

Route::get('/unsubscribe', 'NewsletterController@unsubscribe')->name('newsletter.unsubscribe');
Route::post('/subscribe', 'NewsletterController@subscribe')->name('newsletter.subscribe');