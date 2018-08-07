<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use App\SectionScore;
use App\Season;
use App\Section;
use App\ScoreType;
class SectionScoresTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	Carbon::useMonthsOverflow(false);
        $date = Carbon::createFromDate(2018,6,16);
        $date->subWeeks(34);
        $homework_type = ScoreType::where('name','Homework')->first();
        $quiz_type = ScoreType::where('name','Quiz')->first();
        $exam_type = ScoreType::where('name','Final Exam')->first();
        foreach(Season::with('sections')->get() as $season){
        	$date->subDay(1);

        	foreach($season->sections as $i=>$section){

        		$startDate = $date->copy();
        		if($i>0 && $i<6){
        			$startDate->addDay(1);
        		}else if($i==6){
        			$startDate->addDay(2);
        		}
        		for($j=0;$j<16;$j++){
        			if($j==0){
        				$startDate->addWeek(1);
		    			continue; 
		    		}
		    		if($j>0 && $j<15){
	        			$this->createScore($section->id,$homework_type->id,$startDate->copy(),1);

	        			$this->createScore($section->id,$quiz_type->id,$startDate->copy(),15);
        			}else{
        				$this->createScore($section->id,$exam_type->id,$startDate->copy(),100);
        			}
        			$startDate->addWeek(1);

        		}
        	}
        	$date->addDay(1);
        	$date->addWeek(17);
        }
    }

    private function createScore($section_id,$type_id,$date,$total){
    	$score = new SectionScore();
		$score->date = $date;
		$score->section_id = $section_id;
		$score->total = $total;
		$score->score_type_id = $type_id;
		$score->save();
    }
}
