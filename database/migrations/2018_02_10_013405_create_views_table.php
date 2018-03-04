<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateViewsTable extends Migration{
	/**
	* Run the migrations.
	*
	* @return void
	*/
    public function up(){
        Schema::create('views', function (Blueprint $table) {
			$table->string('identifier', 45);
			$table->integer('meme')->unsigned();
			$table->primary(array('identifier', 'meme'));
        });
    }

	/**
	* Reverse the migrations.
	*
	* @return void
	*/
    public function down(){
        Schema::dropIfExists('views');
    }
}