<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use League\Csv\Reader;
use League\Csv\Statement;
use App\User;
use App\UserType;
use App\Section;
use App\SectionStudent;
use App\Season;
use Log;
use DB;
use Hash;
class ImportStudents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'students:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $path = storage_path('app/imports/s19.csv');
        $season = Season::orderBy('id','desc')->firstOrFail();
        $user_type_id = UserType::where('name','Student')->firstOrFail()->id;

        $csv = Reader::createFromPath($path,'r');
        $csv->setHeaderOffset(0);
        $stmt = new Statement();

        $records = $stmt->process($csv);
        DB::beginTransaction();
        try{

        foreach($records as $offset => $record){
            $validator = Validator::make($record,[
                'Last Name'=>'required',
                'First Name'=>'required',
                'E-mail Address:'=>'required|email|unique:users,email',
                'Which level would you like to enroll this season? '=>'required'
            ]);
            if($validator->fails()){
                if($validator->errors()->has('E-mail Address:')){
                    $user = User::where('email',$record['E-mail Address:'])->first();
                    if($user){
                        Log::info("row $offset : email exists!");
                        Log::info('sheet record', [$record]);
                        Log::info('user', [$user]);
                        $section = $record['Which level would you like to enroll this season? '];
                        $this->addToSection($user,$section,$season);
                        continue;
                    }
                    Log::info('invalid row',[$offset]);
                    continue;
                }
                Log::info('invalid row',[$offset]);

                continue;
            }
            Log::info('new student!');
            $user = new User();
            $user->first_name = title_case($record['First Name']);
            $user->last_name = title_case($record['Last Name']);
            $user->password = Hash::make(str_random(20));
            $user->email = $record['E-mail Address:'];
            $user->user_type_id = $user_type_id;
            $user->save();
            $section = $record['Which level would you like to enroll this season? '];
            $this->addToSection($user,$section,$season);
        }
        }catch(Exception $e){
            DB::rollback();
            throw $e;
        }

    }



    private function addToSection($user,$section_name,$season){
        $section = Section::where('name',$section_name)->where('season_id',$season->id)->first();
        if(!$section){
            $section = new Section();
            $section->name= $section_name;
            $section->season_id= $season->id;
            $section->save();
            Log::info('creating new section..',[$section_name]);
        }
        $section_student = new SectionStudent();
        $section_student->section_id = $section->id;
        $section_student->student_id = $user->id;
        $section_student->save();
        Log::info('student added to section',[$section_student]);
    }
}
