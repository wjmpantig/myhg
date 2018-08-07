<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Faker\Factory;
use App\Season;
use App\SectionAttendance;
use App\StudentAttendance;
use App\SectionStudent;
use App\User;
class StudentAttendancesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker=Factory::create();
        $dates = SectionAttendance::all();
        foreach ($dates as $key => $value) {
        	// $this->command->info($value->id."\t".$value->section_id . "\t" . $value->date);
        	$attendance_id= $value->id;
        	$section_id = $value->section_id;
        	
        	$students = SectionStudent::where('section_id',$section_id)->get();
        	foreach ($students as $key => $student) {
        		// $this->command->info("\t\t" .$student_id);
        		$hasEntry = $faker->boolean(80);
        		// $hasEntry = true;
        		if($hasEntry){
        			$attendance = new StudentAttendance();
        			$attendance->section_attendance_id=$attendance_id;
        			$attendance->student_id = $student->id;
        			$attendance->is_present= $faker->boolean(50);
        			$attendance->save();
        		}
        	}
        	
        }
    }
}
