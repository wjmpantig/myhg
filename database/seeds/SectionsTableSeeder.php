<?php

use Illuminate\Database\Seeder;
use App\Section;
use App\Season;
class SectionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	$seasons = Season::all();
        $names = ['B1','B2','B3','B4','B5','INT1','INT2','ADV'];
        foreach($seasons as $season){
        	foreach($names as $name){
        		$section = new Section();
        		$section->name = $name;
        		$section->season_id = $season->id;
        		$section->save();

        	}
        }
    
    }
}
