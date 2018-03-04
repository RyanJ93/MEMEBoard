<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCategoriesTable extends Migration{
	/**
	* Run the migrations.
	*
	* @return void
	*/
    public function up(){
        Schema::create('category_memes', function(Blueprint $table){
	        $table->increments('id');
            $table->integer('meme_id')->unsigned();
            $table->integer('category_id')->unsigned();
            $table->foreign('meme_id')->on('memes')->references('id')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('category_id')->on('categories')->references('id')->onDelete('cascade')->onUpdate('cascade');
        });
    }

	/**
	* Reverse the migrations.
	*
	* @return void
	*/
    public function down(){
        Schema::dropIfExists('category_memes');
    }
}