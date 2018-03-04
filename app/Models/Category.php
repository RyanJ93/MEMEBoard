<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\MEME;
use App\Models\Comment;
use App\Models\Vote;
use App\Models\User;
use App\Models\Newsletter;

class Category extends Model{
	/**
	* @var string $table A string containing the name of the table mapped by this model.
	*/
    protected $table = 'categories';
    
    /**
	* @var string $primaryKey A string containing the name of the primary key of the table mapped by this model.
	*/
    protected $primaryKey = 'id';
    
    /**
	* Defines the relation between this model and the MEMEs.
	*
	* @return HasMany An instance of the class "Illuminate\Database\Eloquent\Relations\belongsToMany" representing the relationship.
	*/
    public function MEMEs(): \Illuminate\Database\Eloquent\Relations\belongsToMany{
	    return $this->belongsToMany(\App\Models\MEME::class, 'category_memes', 'category_id', 'meme_id');
    }
    
    /**
	* Checks and convert the given categories into instances of this class.
	*
	* @param array $categories A sequential array of strings containing the IDs of the categories.
	* @param array $newCategories A sequential array of strings containing the name of the new categories that shall be created.
	*
	* @return array A sequential array containing instances of this class representing the existing categories and the new categories that have been created.
	*/
    public static function prepareCategories(array $categories, array $newCategories = NULL): array{
	    $ret = array();
	    foreach ( $categories as $key => $value ){
		    $buffer = intval($value);
		    if ( $buffer > 0 && self::where('id', '=', $buffer)->exists() === true ){
			    $ret[] = $buffer;
		    }
	    }
	    if ( $newCategories !== NULL ){
		    foreach ( $newCategories as $key => $value ){
			    try{
				    if ( $value === '' || is_string($value) === false || mb_strlen($value) > 16 ){
					    continue;
				    }
				    $buffer = new Category();
				    $buffer->name = $value;
				    if ( $buffer->save() === true ){
					    $ret[] = $buffer->id;
				    }
			    }catch(\Exception $ex){
				    try{
					    $buffer = self::where('name', '=', $value)->first();
					    $ret[] = $buffer->id;
				    }catch(\Exception $ex){
					    continue;
				    }
			    }
		    }
	    }
	    return $ret;
    }
}