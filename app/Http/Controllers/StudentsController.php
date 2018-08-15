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
use Log;
use DB;
use Validator;
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
			->orderBy('last_name');
		// Log::debug($students->toSql());
    	$students = $students->get();
    	return $students;
    }

    public function get(Request $request){
    	$student_type = UserType::where('name','Student')->first();

    	$student = User::where('user_type_id',$student_type->id)->where('id',$request->id)->firstOrFail();
    	$sections = Section::select('seasons.name as season_name','sections.id as id','sections.name as name')
    		->join('section_students','sections.id','=','section_students.section_id')
    		->join('seasons','seasons.id','=','sections.season_id')
    		->where('student_id',$student->id)
    		->get();
    	$student->sections = $sections;
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
                $a = new StudentAttendance();
                $a->student_id=$id;
                $a->section_attendance_id = $attendance['id'];
                $a->is_present = $attendance['is_present'];
                $a->save();
            }
            $scores = $request->data['scores'];
            foreach($scores as $score){
                
            }
            throw new Exception("nothing");
            DB::commit();
        }catch(Exception $e){
            DB::rollBack();
            throw $e;
        }
        return "something";

    }
}
