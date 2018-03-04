<?php
use Illuminate\Database\Seeder;

class SeedMEMETable extends Seeder{
	/**
	* @var array $categories A sequential array of strings containing all categories that are supported by Lorem Pixel (http://lorempixel.com/).
	*/
	private $categories = array('Abstract', 'Animals', 'Business', 'Cats', 'City', 'Food', 'Nightlife', 'Fashion', 'People', 'Nature', 'Sports', 'Technics', 'Transport');
	
	/**
	* Run the database seeds.
	*/
    public function run(){
	    DB::statement('SET FOREIGN_KEY_CHECKS = 0;');
	    \App\Models\MEME::truncate();
	    \App\Models\User::truncate();
	    \App\Models\Category::truncate();
	    foreach ( $this->categories as $key => $value ){
		    $category = new \App\Models\Category();
		    $category->name = $value;
		    $category->save();
	    }
	    factory(\App\Models\User::class, 5)->create();
        $faker = \Faker\Factory::create();
        for ( $i = 0 ; $i < 30 ; $i++ ){
	        $categories = \App\Models\Category::inRandomOrder()->limit(3)->get();
	        if ( isset($categories[0]) === false ){
		        break;
	        }
	        $meme = new \App\Models\MEME();
	        $meme->title = $faker->name;
	        $meme->path = $faker->imageUrl(800, 800, strtolower($categories[0]->name));
	        $meme->ratio = '1:1';
	        $meme->type = 1;
	        $meme->text = $faker->text(250);
	        $meme->user = \App\Models\User::inRandomOrder()->first()->id;
	        $meme->save();
	        $buffer = array();
	        foreach ( $categories as $key => $value ){
		        $buffer[] = $value->id;
	        }
	        $meme->categories()->attach($buffer);
        }
    }
}