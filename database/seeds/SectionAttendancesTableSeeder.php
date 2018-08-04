<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use App\SectionAttendance;
use App\Season;
use App\Section;
class SectionAttendancesTableSeeder extends Seeder
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
        // $this->command->info($date->toDayDateTimeString());
        // $date->subMonth(15);
        $date->subWeeks(34);
        // $this->command->info($date->toDayDateTimeString());
        // return;
        $seasons = Season::with('sections')->get();
        // $this->command->info('before loop1');
        foreach($seasons as $k=>$season){
        	// $this->command->info($season->name);
        	$date->subDay(1);
            // $this->command->info('before loop2');

        	foreach($season->sections as $i=> $section){
        		$startDate = $date->copy();
        		if($i>0 && $i<6){
        			$startDate->addDay(1);
        		}else if($i==6){
        			$startDate->addDay(2);
        		}
        		// $this->command->info("\t$i $section->name");
        		
        		// $this->command->info("\t\t".$startDate->toDayDateTimeString());
        		for($j=0;$j<16;$j++){
        			// $this->command->info("\t\t".($j).' '.$startDate->toDayDateTimeString());
        			$attendance = new SectionAttendance();
        			$attendance->date = $startDate->copy();
        			$attendance->section_id = $section->id;
        			$attendance->save();
        			$startDate->addWeek(1);
        		}
        		
        	}
        	$date->addDay(1);
        	$date->addWeek(17);
        	// $this->command->info(" ");
        }
    }
}
