<?php

use Illuminate\Database\Seeder;

use League\Csv\Reader;
use League\Csv\Statement;
use Faker\Factory;


use App\UserType;
use App\User;
use App\Section;
use App\SectionStudent;
use App\Season;

class UsersTableSeeder extends Seeder
{

	private $student_type = null;
	private $faker = null;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	$this->faker = Factory::create();

    	$admin_type = UserType::where('name','Administrator')->first();
		$this->student_type = UserType::where('name','Student')->first();

        $admin = new User();
        $admin->first_name = "Admin";
        $admin->last_name="Not admin";
        $admin->email="hello@winfred.work";
        $admin->user_type_id = $admin_type->id;
        $admin->password=bcrypt("fiddle");
        $admin->save();

        $admin = new User();
        $admin->first_name = "educ";
        $admin->last_name="learnkoreanph";
        $admin->email="educ.learnkoreanph@gmail.com";
        $admin->user_type_id = $admin_type->id;
        $admin->password=bcrypt("yougotmii");
        $admin->save();

        
        try{
        	DB::beginTransaction();	
        	$this->createStudents();
			DB::commit();
        }catch(Exception $exception){
        	DB::rollback();
        	throw $exception;
        }
		
		
		
    }

    private function createStudents(){
	  $seasons = Season::all();
        foreach($seasons as $season){
        	$sections = Section::where('season_id',$season->id)->get();
        	foreach($sections as $section){
        		if(!strcmp($season->name,"18th") && !strcmp($section->name,"Advanced")){
        			$path = storage_path('app/imports/students-advanced.csv');
					$this->importUsersFromCSV($path,$section->id);
        		}else{
        			$n = $this->faker->numberBetween(10,20);
        			for($i=0;$i<$n;$i++){
	        			$student = $this->createFakeStudent();
	        			$section_student = new SectionStudent();
	        			$section_student->student_id = $student->id;
	        			$section_student->section_id = $section->id;
	        			$section_student->save();
	        		}
        		}
        	}
        }
    }

    private function createFakeStudent(){
    	$user = new User();
    	$user->first_name = $this->faker->firstName();
    	$user->last_name = $this->faker->lastName();
    	$user->email = sprintf("%s.%s%d@gmail.com",str_slug($user->first_name),str_slug($user->last_name),$this->faker->randomNumber(2));
    	$user->password = bcrypt(str_random(20));
    	$user->user_type_id = $this->student_type->id;
    	$user->save();
    	return $user;
    }

    private function importUsersFromCSV($path,$section_id){
    	$csv = Reader::createFromPath($path,'r');
		$csv->setHeaderOffset(0);
		$stmt = new Statement();

		$records = $stmt->process($csv);
		foreach($records as $offset => $record){
			$validator = Validator::make($record,[
	    		'email'=>'email|required',
	    		'first name'=>'required',
	    		'last name'=>'required'
	    	]);
			if($validator->fails()){
				Log::debug($validator->errors());
				continue;
			}
			$user = new User();
			$user->first_name = title_case($record['first name']);
			$user->last_name = title_case($record['last name']);
			$user->password = bcrypt(str_random(20));
			$user->email = $record['email'];
			$user->user_type_id = $this->student_type->id;
			$user->save();

			$section_student = new SectionStudent();
			$section_student->student_id = $user->id;
			$section_student->section_id = $section_id;
			$section_student->save();
		}

    }

}
