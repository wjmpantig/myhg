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
        	
        	
        	$section_id = $section_score->section_id;
        	// $this->command->info("section: $section_id");
        	$students = SectionStudent::where('section_id',$section_id);
        	// if($section_id == 16){
        	// 	Log::debug($students->get());
        	// }
        	$students = $students->get();
        	foreach ($students as $key => $student) {
        		
        		$hasEntry = $faker->boolean(80);
        		
        		if($hasEntry){
        			$score = new StudentScore();
        			$score->section_score_id=$section_score->id;
        			$score->student_id = $student->student_id;
        			$score->score = $faker->numberBetween(0,$section_score->total);
        			$score->save();
        			// $this->command->info("\t$section_score->id\t$student->id");
        		}
        	}
        	
        }
    }
}
