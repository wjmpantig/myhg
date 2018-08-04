<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Section;
use App\SectionAttendance;
use App\StudentAttendance;
use App\Season;
use App\User;
use App\UserType;
use DB;
class SectionsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function all(Request $request){
    	$season = empty($request->season_id) ? Season::orderBy('created_at','desc')->first() : Season::findOrFail($request->season_id);
		$sections = Section::where('season_id',$season->id)->get();
		return $sections;
    }

    public function update(Request $request){
    	$request->validate([
    		'id'=>'exists:sections,id',
    		'name'=>'required|max:20'
    	]);
    	$section = Section::findOrFail($request->id);
    	$section->name = $request->name;
    	$section->save();
    	return $section;
    }

    public function delete(Request $request){
    	$request->validate([
    		'id'=>'exists:sections,id'
    	]);
    	$section = Section::findOrFail($request->id);
    	$section->delete();
    	return "Delete success";
    }

    public function get(Request $request){
    	$request->validate([
    		'id'=>'exists:sections,id'
    	]);
    	$section = Section::findOrFail($request->id);
    	return $section;
    }


    public function students(Request $request){
    	$request->validate([
    		'id'=>'exists:sections,id'
    	]);
    	$student_type = UserType::where('name','Student')->first();
    	$students = DB::table('users')
			->join('section_students','student_id','=','users.id')
    		->where('user_type_id',$student_type->id)
    		->where('section_id',$request->id)
    		->orderBy('last_name','asc')
    		->get();
    	return $students;
    		
    }

    public function attendance(Request $request){
    	$request->validate([
    		'id'=>'exists:sections,id'
    	]);
    	
    	
    	$dates = SectionAttendance::with('attendance')
			->where('section_id',$request->id)
			->get();
		$date_ids = array_pluck($dates,'id');
		$students = DB::table('users')
    		->rightJoin('section_students','student_id','=','users.id')
    		->where('section_id',$request->id)
    		->orderBy('last_name')
    		->get();
    	$student_ids = array_pluck($students,'id');
    	$attendance = StudentAttendance::whereIn('student_id',$student_ids)->get();
    	$data = new \stdClass();
    	$data->dates = $dates;
    	$data->students = $students;
    	$data->attendance = $attendance;
    	// $data->ids = $date_ids;
    	return response()->json($data);
    }
}
