<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVotesTable extends Migration{
	/**
	* Run the migrations.
	*
	* @return void
	*/
    public function up(){
        Schema::create('votes', function(Blueprint $table){
            $table->increments('id');
            $table->boolean('positive');
            $table->integer('user')->unsigned();
            $table->integer('meme')->unsigned();
            $table->timestamps();
            $table->foreign('user')->on('users')->references('id')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('meme')->on('memes')->references('id')->onDelete('cascade')->onUpdate('cascade');
        });
    }

	/**
	* Reverse the migrations.
	*
	* @return void
	*/
    public function down(){
        Schema::dropIfExists('votes');
    }
}