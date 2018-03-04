<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMEMETable extends Migration{
	/**
	* Run the migrations.
	*
	* @return void
	*/
    public function up(){
        Schema::create('memes', function(Blueprint $table){
	        $table->string('title', 32);
	        $table->string('path', 64);
	        $table->integer('type');
	        $table->integer('up_votes')->unsigned()->default(0);
	        $table->integer('down_votes')->unsigned()->default(0);
	        $table->integer('comments')->unsigned()->default(0);
	        $table->integer('views')->unsigned()->default(0);
	        $table->string('ratio', 10)->nullable(false)->default('1:1');
	        $table->integer('user')->unsigned()->nullable();
	        $table->text('text');
	        $table->foreign('user')->on('users')->references('id');
            $table->increments('id');
            $table->timestamps();
        });
    }

	/**
	* Reverse the migrations.
	*
	* @return void
	*/
    public function down(){
        Schema::dropIfExists('memes');
    }
}
