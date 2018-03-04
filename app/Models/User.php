<?php
namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use App\Models\MEME;
use App\Models\Comment;
use App\Models\Vote;
use App\Models\Newsletter;
use App\Models\Category;

use DB;
use Log;

class User extends Authenticatable{
    use Notifiable;
    
    /**
	* @var string $table A string containing the name of the table mapped by this model.
	*/
    protected $table = 'users';
    
    /**
	* @var string $primaryKey A string containing the name of the primary key of the table mapped by this model.
	*/
	protected $primaryKey = 'id';
	
	/**
	* @var array $fillable A sequrential array of strings containing the attributes that are mass assignable.
	*/
    protected $fillable = array('name', 'email', 'password');
    
    /**
	* @var array $hidden A sequrential array of strings containing the attributes that shall not be included while reading User properties.
	*/
    protected $hidden = array('password', 'remember_token');
    
    /**
	* Defines the relation between this model and the MEMEs.
	*
	* @return HasMany An instance of the class "Illuminate\Database\Eloquent\Relations\HasMany" representing the relationship.
	*/
    public function elements(): \Illuminate\Database\Eloquent\Relations\HasMany{
	    return $this->hasMany(MEME::class, 'user', 'id');
    }
    
    /**
	* Defines the relation between this model and the comments.
	*
	* @return HasMany An instance of the class "Illuminate\Database\Eloquent\Relations\HasMany" representing the relationship.
	*/
    public function comments(): \Illuminate\Database\Eloquent\Relations\HasMany{
	    return $this->hasMany(Comment::class, 'user', 'id');
    }
    
    /**
	* Defines the relation between this model and the votes.
	*
	* @return HasMany An instance of the class "Illuminate\Database\Eloquent\Relations\HasMany" representing the relationship.
	*/
    public function votes(): \Illuminate\Database\Eloquent\Relations\HasMany{
	    return $this->hasMany(Vote::class, 'user', 'id');
    }
    
    /**
	* Defines an accessor used to get user's full name.
	*
	* @return string A string containing the user's full name.
	*/
    public function getFullNameAttribute(): string{
	    return $this->name === '' || $this->surname === '' ? ( $this->name . $this->surname ) : ( $this->name . ' ' . $this->surname );
    }
    
    /**
	* Removes all contents created by this user (MEMEs, comments, views and votes), this method is chainable.
	*
	* @throws Exception If an error occurrs while removing user contents.
	*/
    public function removeContents(): User{
	    try{
		    DB::statement('DELETE FROM views INNER JOIN memes ON views.meme = memes.id WHERE memes.user = ?;', array($this->id));
		    DB::statement('DELETE FROM votes INNER JOIN memes ON votes.meme = memes.id WHERE memes.user = ?;', array($this->id));
		    DB::statement('DELETE comments FROM comments INNER JOIN memes ON comments.meme = memes.id WHERE memes.user = ?;', array($this->id));
		    DB::statement('DELETE category_memes FROM category_memes INNER JOIN memes ON category_memes.meme_id = memes.id WHERE memes.user = ?;', array($this->id));
		    DB::statement('DELETE FROM memes WHERE user = ?;', array($this->id));
		    DB::statement('DELETE FROM comments WHERE user = ?;', array($this->id));
		    return $this;
	    }catch(\Exception $ex){
		    if ( env('APP_DEBUG', false) === true ){
				Log::debug($ex->getFile() . ': ' . $ex->getLine() . ': ' . $ex->getMessage());
			}
			throw new \Exception('An error occurred while removing user contents.', NULL, $ex);
	    }
    }
    
    /**
	* Sets admin rights for the user, this method is chainable.
	*
	* @param bool $value If set to "true", this user will be promoted to admin, otherwise admin rights will be removed, note that this will cause all user MEMEs to be removed.
	*
	* @throws Exception If an error occurrs while setting admin rights.
	*/
    public function setAdminRights(bool $value = true): User{
	    try{
		    if ( $value === false ){
			    DB::statement('DELETE votes FROM votes INNER JOIN memes ON votes.meme = memes.id WHERE memes.user = ?;', array($this->id));
			    DB::statement('DELETE comments FROM comments INNER JOIN memes ON comments.meme = memes.id WHERE memes.user = ?;', array($this->id));
			    DB::statement('DELETE category_memes FROM category_memes INNER JOIN memes ON category_memes.meme_id = memes.id WHERE memes.user = ?;', array($this->id));
			    DB::statement('DELETE views FROM views INNER JOIN memes ON views.meme = memes.id WHERE memes.user = ?;', array($this->id));
			    DB::statement('DELETE FROM memes WHERE user = ?;', array($this->id));
		    }
		    $this->admin = $value === true ? 1 : 0;
		    $this->save();
		    return $this;
	    }catch(\Exception $ex){
		    if ( env('APP_DEBUG', false) === true ){
				Log::debug($ex->getFile() . ': ' . $ex->getLine() . ': ' . $ex->getMessage());
			}
			throw new \Exception('An error occurred while setting admin rights for the user.', NULL, $ex);
	    }
    }
}