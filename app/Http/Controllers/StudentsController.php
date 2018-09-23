<?php

namespace App\Http\Controllers;

use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use App\User;
use App\UserType;
use App\Season;
use App\Student_Section;
use App\Section;
use App\SectionStudent;
use App\SectionAttendance;
use App\StudentAttendance;
use App\SectionScore;
use App\StudentScore;
use App\ScoreType;
use Auth;
use Log;
use DB;
use Validator;
use Faker\Factory;
class StudentsController extends Controller
{
    public function all(Request $request){
    	// Log::debug($request->season_id);
    	$season = empty($request->season_id) ? Season::orderBy('created_at','desc')->first() : Season::findOrFail($request->season_id);
    	$student_type = UserType::where('name','Student')->first();
        $students  = User::select('users.id',DB::raw('concat(last_name,", ",first_name) as name'),'sections.name as section_name','season_id')
            ->join('section_students','users.id','=','section_students.student_id')
            ->join('sections','sections.id','=','section_students.section_id')
            ->where('user_type_id',$student_type->id)
            ->where('season_id',$season->id)
            ->whereNull('section_students.deleted_at');

        if(!empty(trim($request->q))){
            $q = strtoupper(trim($request->q));
            $students = $students->where(function($query) use ($q){
                    $query->whereRaw('UPPER(first_name) LIKE ?',["%$q%"])
                        ->orWhereRaw('UPPER(last_name) LIKE ?',["%$q%"]);
                });

        }

    	$students = $students->orderBy('last_name')->paginate(20);
    	return $students;
    }

    public function get(Request $request){
    	$student_type = UserType::where('name','Student')->first();

    	$student = User::where('user_type_id',$student_type->id)->where('id',$request->id)->firstOrFail();
    	$sections = Section::select('seasons.name as season_name','sections.id as id','sections.name as name')
    		->join('section_students','sections.id','=','section_students.section_id')
    		->join('seasons','seasons.id','=','sections.season_id')
    		->where('student_id',$student->id)
            ->whereNull('section_students.deleted_at')
    		->get();
    	$student->sections = $sections;
    	return $student;
    }

    public function create(Request $request){
        $request->validate([
            'first_name'=>'required|max:100',
            'last_name'=>'required|max:100',
            'section'=>'required|exists:sections,id'
        ]);
        $student_type = UserType::where('name','Student')->first();
        $faker = Factory::create();
        $user = new User();
        $user->first_name=trim($request->first_name);
        $user->last_name=$request->last_name;
        $user->user_type_id = $student_type->id;
        $user->password = bcrypt($faker->password);

        $user->email = $this->generateRandomEmail($user->first_name,$user->last_name,$faker);
        
        $user->save();
        $section_student = new SectionStudent();
        $section_student->student_id = $user->id;
        $section_student->section_id = $request->section;
        $section_student->save();
        Log::info(sprintf("user %d created student %d in section %d",Auth::user()->id,$user->id,$request->section));

        return $user;
    }

    private function generateRandomEmail($first_name,$last_name,$faker = null){
        if (!$faker){
            $faker = Factory::create();
        }
    
        do{
            $email = sprintf("%s.%s%d@gmail.com",str_slug($first_name),str_slug($last_name),$faker->randomNumber(5));
            $user = User::where('email',$email)->first();
            if($user){
                continue;
            }
            break;
        }while(true);
        return $email;
    }

    public function update(Request $request){
        $request->validate([
            'id'=>'exists:users,id',
            'first_name'=>'sometimes|required|max:100',
            'last_name'=>'sometimes|required|max:100',
            'email'=>[
                'sometimes',
                'required',
                'email',
                'max:100',
                Rule::unique('users')->ignore($request->id)
            ]
        ]);
        $student_type = UserType::where('name','Student')->first();
        $student = User::where('user_type_id',$student_type->id)->where('id',$request->id)->firstOrFail();
        $has_changes = false;

        $fields = ['first_name','last_name','email'];
        foreach($fields as $field){
            if($request->filled($field)){
                $student[$field] = trim($request[$field]);
                $has_changes = true;
            }
        }
        
        if($request->filled('last_name')){
            $student->last_name = trim($request->last_name);
        }
        if($request->filled('email')){
            $student->email = trim($request->email);
        }
        $student->save();
        Log::info(sprintf("user %d updated student %d",Auth::user()->id,$student->id));

        return $student;
    }

    public function attendance(Request $request){
        $student_type = UserType::where('name','Student')->first();
        $dates = SectionAttendance::where('section_id',$request->section_id)->get();
        $date_ids = array_pluck($dates,'id');
        $attendance = StudentAttendance::whereIn('section_attendance_id',$date_ids)
            ->where('student_id',$request->id)
            ->get();
        $attendance = $attendance->mapWithKeys(function($item){
            return [$item['section_attendance_id']=>[
                'id'=>$item['section_attendance_id'],
                'is_present'=>$item['is_present']
            ]];
        });
        $resp = new \stdClass();
        $resp->dates = $dates->isEmpty() ? new\stdClass() : $dates;
        $resp->attendance = $attendance->isEmpty() ? new\stdClass() : $attendance;
        return response()->json($resp);
    }

    public function scores(Request $request){
        // $score_types = ScoreType::all();
        $scores= SectionScore::where('section_id',$request->section_id)->get();
        $score_ids = array_pluck($scores,'id');
        $student_scores = StudentScore::whereIn('section_score_id',$score_ids)
            ->where('student_id',$request->id)
            ->get();

        $scores = $scores->groupBy('score_type_id');

        $scores = $scores->map(function($item,$key){
            $item = $item->mapWithKeys(function($item){
                return [$item['id']=>[
                    'id'=>$item['id'],
                    'date'=>$item['date'],
                    'total'=>$item['total']
                ]];
            });
            return $item;
        });

        
        $student_scores = $student_scores->mapWithKeys(function($item,$key){
            return [$item['section_score_id']=>[
                'id'=>$item['section_score_id'],
                'score'=>$item['score']
            ]];
        });

        $resp = new \stdClass();
        $resp->totals = $scores->isEmpty() ? new\stdClass() : $scores;
        $resp->scores = $student_scores->isEmpty() ? new\stdClass() : $student_scores;
        return response()->json($resp);
        
    }

    public function transfer(Request $request){

        $id = $request->id;
        $from_section_id = $request->from_section_id;
        $target_section_id = $request->target_section_id;
        $request->validate([
            'id'=>[
                Rule::unique('section_students')->where(function($query) use($id,$from_section_id){
                    return $query->where('student_id',$id)
                        ->where('section_id',$from_section_id)
                        ->whereNull('deleted_at');
                })
            ],
            'target_section_id'=>[
                "required",
                Rule::exists('sections','id')->where(function($query) use ($from_section_id,$target_section_id){
                    $query->where('id',$target_section_id)->whereNotIn('id',[$from_section_id]);
                    // Log::debug($query->toSql());
                })
            ],
            'data.attendance.*.id'=>"exists:section_attendances,id",
            'data.attendance.*.is_present'=>'boolean',
            'data.scores.*'=>[
                function($attrs,$val,$fail){
                    
                    $total = SectionScore::find($val['id']);
                    // Log::debug($total);
                    if(!$total){
                        // Log::debug("empty id");
                        return $fail("$attrs is invalid.");
                    }
                    $score = $val['score'];
                    if(!empty($score) && !is_numeric($score)){
                        // Log::debug("non numeric");
                        return $fail("$attrs is invalid.");
                    }
                    if($score < 0 || $score > $total->total ){
                        // Log::debug("not between 0-$total->total");
                        return $fail("$attrs must be between 0 and $total->total");
                    }

                    // Log::debug("end;");
                }
            ]
            
        ]);
        $user = User::findOrFail($request->id);
        try{
            DB::beginTransaction();
            $from_section = SectionStudent::where('student_id',$id)
                ->where('section_id',$from_section_id)
                ->firstOrFail();
            $from_section->delete();
            $attendances = $request->data['attendance'];
            foreach($attendances as $attendance){
                if(is_null($attendance['is_present'])){
                    continue;
                }
                $a = StudentAttendance::where('student_id',$id)
                    ->where('section_attendance_id',$attendance['id'])->first();
                if(!$a){
                    $a = new StudentAttendance();
                    $a->student_id=$id;
                    $a->section_attendance_id = $attendance['id'];
                }
                $a->is_present = $attendance['is_present'];
                $a->save();
            }
            $scores = $request->data['scores'];
            foreach($scores as $score){
                if(is_null( $score['score'])){
                    continue;
                }
                $s = StudentScore::where('student_id',$id)
                    ->where('section_score_id',$score['id'])->first();
                if(!$s){
                    $s = new StudentScore();
                    $s->section_score_id = $score['id'];
                    $s->student_id=$id;
                }
                $s->score = $score['score'];
                $s->save();
            }
            $target_section = new SectionStudent();
            $target_section->student_id = $id;
            $target_section->section_id = $target_section_id;
            $target_section->save();
            DB::commit();
        }catch(Exception $e){
            DB::rollBack();
            throw $e;
        }
        Log::info(sprintf("user %d transfered student %d from section %d to %d",Auth::user()->id,$id,$from_section_id,$target_section_id));

        return "transfer complete";

    }
}
