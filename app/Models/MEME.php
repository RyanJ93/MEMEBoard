<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\User;
use App\Models\Comment;
use App\Models\Vote;
use App\Models\Newsletter;
use App\Models\Category;

use DB;
use Log;

class MEME extends Model{
	/**
	* @var string $table A string containing the name of the table mapped by this model.
	*/
	protected $table = 'memes';
	
	/**
	* @var string $primaryKey A string containing the name of the primary key of the table mapped by this model.
	*/
	protected $primaryKey = 'id';
	
	/**
	* Defines the relation between this model and the comments.
	*
	* @return HasMany An instance of the class "Illuminate\Database\Eloquent\Relations\HasMany" representing the relationship.
	*/
    public function comments(): \Illuminate\Database\Eloquent\Relations\HasMany{
	    return $this->hasMany(\App\Models\Comment::class, 'meme', 'id');
    }
    
    /**
	* Defines the relation between this model and the user.
	*
	* @return BelongsTo An instance of the class "Illuminate\Database\Eloquent\Relations\BelongsTo" representing the relationship.
	*/
    public function creator(): \Illuminate\Database\Eloquent\Relations\BelongsTo{
	    return $this->belongsTo(\App\Models\User::class, 'user', 'id');
    }
    
    /**
	* Defines the relation between this model and the categories.
	*
	* @return BelongsToMany An instance of the class "Illuminate\Database\Eloquent\Relations\BelongsToMany" representing the relationship.
	*/
    public function categories(): \Illuminate\Database\Eloquent\Relations\BelongsToMany{
	    return $this->belongsToMany(\App\Models\Category::class, 'category_memes', 'meme_id', 'category_id');
    }
    
    /**
	* Defines the relation between this model and the vote.
	*
	* @return HasMany An instance of the class "Illuminate\Database\Eloquent\Relations\HasMany" representing the relationship.
	*/
    public function votes(): \Illuminate\Database\Eloquent\Relations\HasMany{
	    return $this->hasMany(\App\Models\Vote::class, 'meme', 'id');
    }
    
    /**
	* Defines an accessor used to get the element's processed path (if is a relative URL, adds the directory name)
	*
	* @return string A string containing the pathe to the MEME file.
	*/
    public function getProcessedPathAttribute(): string{
	    return $this->relativePath === false ? $this->path : ( env('MEME_PATH', '') . '/' . $this->user . '/' . $this->path );
    }
    
    /**
	* Defines an accessor used to check if the path of the file of this MEME is absolute (an online link mainly used in testing with seeder) or relative.
	*
	* @return bool If the path is relative will be returned "true", otherwise "false".
	*/
    public function getRelativePathAttribute(): bool{
	    return mb_strpos($this->path, 'http') === 0 ? false : true;
    }
    
    /**
	* Increments the view counter of the MEME.
	*
	* @param string $identifier A string containing the identifier of the visitor, usually his IP address or user ID if is an authenticated user.
	*
	* @return bool If the counter has been increased will be returned "true", otherwise "false".
	*/
    public function incrementViewsCounter(string $identifier = NULL): bool{
		try{
			if ( $identifier === NULL || $identifier === '' ){
				return false;
			}
			$buffer = DB::table('views')->where('meme', '=', $this->id)->where('identifier', '=', $identifier)->count();
			if ( $buffer !== 0 ){
				return false;
			}
			DB::table('views')->insert(array(
				'meme' => $this->id,
				'identifier' => $identifier
			));
			$this->views = $this->views + 1;
			return $this->save() === true ? true : false;
		}catch(\Exception $ex){
			if ( env('APP_DEBUG', false) === true ){
				Log::debug($ex->getFile() . ': ' . $ex->getLine() . ': ' . $ex->getMessage());
			}
			return false;
		}
	}
	
	/**
	* Toggles a positive or negative vote on the MEME.
	*
	* @param bool $positive If set to "true" it means that the vote is positive, otherwise is negative.
	* @param User $user An instance of the class "App\Models\User" representing the authenticated user.
	*
	* @return bool If the vote has been created will be returned "true", otherwise, if the vote is already existing will be returned "false".
	*
	* @throws Exception If an error occurrs while creating the vote.
	*/
	public function toggleVote(bool $positive, User $user): bool{
		try{
		    $votes = Vote::where('user', '=', $user->id)->where('meme', '=', $this->id)->first();
		    $create = true;
		    if ( $votes !== NULL ){
			    if ( $votes->positive === 1 ){
				    $this->up_votes = $this->up_votes - 1;
				    if ( $this->up_votes < 0 ){
					    $this->up_votes = 0;
				    }
				    if ( $positive === true ){
					    $create = false;
				    }
			    }else{
				    $this->down_votes = $this->down_votes - 1;
				    if ( $this->down_votes < 0 ){
					    $this->down_votes = 0;
				    }
				    if ( $positive === false ){
					    $create = false;
				    }
			    }
			    if ( $votes->delete() === false ){
				    throw new \Exception('An error occurred while voting the element.');
			    }
		    }
		    if ( $create === true ){
			    $vote = new Vote();
				$vote->positive = $positive === true ? 1 : 0;
				$vote->creator()->associate($user);
				$vote->element()->associate($this);
				if ( $vote->save() === false ){
					throw new \Exception('An error occurred while voting the element.');
				}
			    if ( $positive === true ){
				    $this->up_votes = $this->up_votes + 1;
			    }else{
				    $this->down_votes = $this->down_votes + 1;
			    }
		    }
		    if ( $this->save() === false ){
				throw new \Exception('An error occurred while voting the element.');
			}
			return $create;
		}catch(\Exception $ex){
			if ( env('APP_DEBUG', false) === true ){
				Log::debug($ex->getFile() . ': ' . $ex->getLine() . ': ' . $ex->getMessage());
			}
			throw new \Exception('An error occurred while voting the element.', NULL, $ex);
		}
	}
}