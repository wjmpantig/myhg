<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

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

        $path = storage_path('app/imports/admins.csv');
        $this->importUsersFromCSV($path,$admin_type->id);
        
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
					$this->importUsersFromCSV($path,$this->student_type->id,$section->id);
        		}else if(!strcmp($season->name,"18th") && !strcmp($section->name,"B1")){
                    $path = storage_path('app/imports/students-b1.csv');
                    $this->importUsersFromCSV($path,$this->student_type->id,$section->id);
                }else if(!strcmp($season->name,"18th") && !strcmp($section->name,"B2")){
                    $path = storage_path('app/imports/students-b2.csv');
                    $this->importUsersFromCSV($path,$this->student_type->id,$section->id);
                }else if(!strcmp($season->name,"18th") && !strcmp($section->name,"B3")){
                    $path = storage_path('app/imports/students-b3.csv');
                    $this->importUsersFromCSV($path,$this->student_type->id,$section->id);
                }else if(!strcmp($season->name,"18th") && !strcmp($section->name,"B4")){
                    $path = storage_path('app/imports/students-b4.csv');
                    $this->importUsersFromCSV($path,$this->student_type->id,$section->id);
                }else if(!strcmp($season->name,"18th") && !strcmp($section->name,"B5")){
                    $path = storage_path('app/imports/students-b5.csv');
                    $this->importUsersFromCSV($path,$this->student_type->id,$section->id);
                }else if(!strcmp($season->name,"18th") && !strcmp($section->name,"Intermediate")){
                    $path = storage_path('app/imports/students-intermediate.csv');
                    $this->importUsersFromCSV($path,$this->student_type->id,$section->id);
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
    	$user->email =$this->generateRandomEmail($user->first_name,$user->last_name,$this->faker);
    	$user->password = Hash::make(str_random(20));
    	$user->user_type_id = $this->student_type->id;
    	$user->save();
    	return $user;
    }

    private function importUsersFromCSV($path,$user_type_id,$section_id=null){
    	$csv = Reader::createFromPath($path,'r');
		$csv->setHeaderOffset(0);
		$stmt = new Statement();

		$records = $stmt->process($csv);
		foreach($records as $offset => $record){
			$validator = Validator::make($record,[
	    		'email'=>'email|required|unique:users,email',
	    		'first name'=>'required',
	    		'last name'=>'required'
	    	]);
			if($validator->fails()){
				// Log::debug($validator->errors());
                if($validator->errors()->has('email')){
                    // Log::debug('generating fake email..');
                    $email = $this->generateRandomEmail($record['first name'],$record['last name'],$this->faker);
                    $record['email'] =  $email;
                    // Log::debug("fake email generated: $email");
                }else{
    				continue;
                }
			}
			$user = new User();
			$user->first_name = title_case($record['first name']);
			$user->last_name = title_case($record['last name']);
            if(isset($record['password'])){
                $user->password = Hash::make($record['password']);
            }else{
                $user->password = Hash::make(str_random(20));
            }
			$user->email = $record['email'];
			$user->user_type_id = $user_type_id;
			$user->save();
            if($section_id){
    			$section_student = new SectionStudent();
    			$section_student->student_id = $user->id;
    			$section_student->section_id = $section_id;
    			$section_student->save();
            }
		}

    }

    private function generateRandomEmail($first_name,$last_name,$faker = null){
        if (!$faker){
            $faker = Factory::create();
        }
        do{
            $email = sprintf("%s.%s%d@fakemail.com",str_slug($first_name),str_slug($last_name),$faker->randomNumber(2));
            $user = User::where('email',$email)->first();
            if($user){
                continue;
            }
            break;
        }while(true);
        return $email;
    }


}
