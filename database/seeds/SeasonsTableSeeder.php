<?php

use Illuminate\Database\Seeder;
use App\Season;
class SeasonsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $names = ['16th', '17th','18th'];
        if(App::environment(['production','staging'])){
            $names = ['18th'];
        }
        foreach($names as $name){
        	$season = new Season();
        	$season->name=$name;
        	$season->save();
        }
    }
}
