<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Faker\Factory;
use App\Season;
use App\SectionScore;
use App\SectionStudent;
use App\StudentScore;
class StudentScoresTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker=Factory::create();
        $section_scores = SectionScore::all();
        foreach ($section_scores as $key => $section_score) {
        	// $this->command->info($value->id."\t".$value->section_id . "\t" . $value->date);
        	
        	$section_id = $section_score->section_id;
        	
        	$students = SectionStudent::where('section_id',$section_id)->get();
        	foreach ($students as $key => $student) {
        		// $this->command->info("\t\t" .$student_id);
        		$hasEntry = $faker->boolean(80);
        		// $hasEntry = true;
        		if($hasEntry){
        			$score = new StudentScore();
        			$score->section_score_id=$section_score->id;
        			$score->student_id = $student->id;
        			$score->score = $faker->numberBetween(0,$section_score->total);
        			$score->save();
        		}
        	}
        	
        }
    }
}
