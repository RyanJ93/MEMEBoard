<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommentsTable extends Migration{
	/**
	* Run the migrations.
	*
	* @return void
	*/
    public function up(){
        Schema::create('comments', function (Blueprint $table) {
            $table->increments('id');
            $table->text('text');
            $table->integer('user')->unsigned()->nullable();
            $table->integer('meme')->unsigned();
            $table->timestamps();
            $table->foreign('user')->on('users')->references('id');
            $table->foreign('meme')->on('memes')->references('id')->onDelete('cascade')->onUpdate('cascade');
        });
    }
	
	/**
	* Reverse the migrations.
	*
	* @return void
	*/
    public function down(){
        Schema::dropIfExists('comments');
    }
}