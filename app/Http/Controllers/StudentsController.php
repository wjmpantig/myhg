<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\UserType;
use App\Season;
use App\Student_Section;
use App\Section;
use Log;
use DB;
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

}
