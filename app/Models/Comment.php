<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\MEME;
use App\Models\User;
use App\Models\Vote;
use App\Models\Newsletter;
use App\Models\Category;

class Comment extends Model{
	/**
	* @var string $table A string containing the name of the table mapped by this model.
	*/
	protected $table = 'comments';
	
	/**
	* @var string $primaryKey A string containing the name of the primary key of the table mapped by this model.
	*/
	protected $primaryKey = 'id';
	
	/**
	* Defines the relation between this model and the MEME.
	*
	* @return BelongsTo An instance of the class "Illuminate\Database\Eloquent\Relations\BelongsTo" representing the relationship.
	*/
    public function element(): \Illuminate\Database\Eloquent\Relations\BelongsTo{
	    return $this->belongsTo(\App\Models\MEME::class, 'meme', 'id');
    }
    
    /**
	* Defines the relation between this model and the user.
	*
	* @return BelongsTo An instance of the class "Illuminate\Database\Eloquent\Relations\BelongsTo" representing the relationship.
	*/
    public function creator(){
	    return $this->belongsTo(\App\Models\User::class, 'user', 'id');
    }
}