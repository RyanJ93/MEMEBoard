<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNewsletterTable extends Migration{
	/**
	* Run the migrations.
	*
	* @return void
	*/
    public function up(){
        Schema::create('newsletter', function(Blueprint $table){
            $table->string('email', 256)->unique();
            $table->string('token', 256)->unique();
            $table->timestamps();
            $table->primary('email');
        });
    }

	/**
	* Reverse the migrations.
	*
	* @return void
	*/
    public function down(){
        Schema::dropIfExists('newsletter');
    }
}