<?php

namespace App\Http\Controllers;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Section;
use App\SectionAttendance;
use App\StudentAttendance;
use App\Season;
use App\ScoreType;
use App\SectionScore;
use App\User;
use App\UserType;
use Carbon\Carbon;
use Auth;
use DB;
use Log;
class SectionsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function all(Request $request){
    	$season = empty($request->season_id) ? Season::orderBy('created_at','desc')->first() : Season::findOrFail($request->season_id);
		$sections = Section::where('season_id',$season->id);
        if($request->except){
            $sections = $sections->whereNotIn('id',[$request->except]);
        }
		return $sections->get();
    }

    public function update(Request $request){
    	$request->validate([
    		'id'=>'required|exists:sections,id',
    		'name'=>'required|max:20'
    	]);
    	$section = Section::findOrFail($request->id);
    	$section->name = $request->name;
    	$section->save();
        Log::info(sprintf("user %d updated section %d",Auth::user()->id,$request->id));
    	return $section;
    }

    public function delete(Request $request){
    	$request->validate([
    		'id'=>'required|exists:sections,id'
    	]);
    	$section = Section::findOrFail($request->id);
    	$section->delete();
        Log::info(sprintf("user %d deleted section %d",Auth::user()->id,$request->id));
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
            ->whereNull('section_students.deleted_at')
    		->orderBy('last_name','asc');
        if(!empty(trim($request->q))){
            $q = trim($request->q);
             $students = $students->where(function($query) use ($q){
                    $query->whereRaw('UPPER(first_name) LIKE ?',["%$q%"])
                        ->orWhereRaw('UPPER(last_name) LIKE ?',["%$q%"]);
                });
        }
    	return $students->paginate(20);
    		
    }

    public function attendance(Request $request){
    	$request->validate([
    		'id'=>'exists:sections,id'
    	]);
    	
    	
    	$dates = SectionAttendance::where('section_id',$request->id)
			->orderBy('date')
			->get();
		
		// $raw = "(select group_concat( section_attendances.id separator ',') from student_attendances 
		// 	join section_attendances on section_attendances.id = student_attendances.section_attendance_id
		// 	where student_attendances.student_id = users.id and is_present = 1 and section_attendances.deleted_at is null
		// 	group by student_id
		// 	order by date) as attendance
		// ";
        Log::debug($dates);
		$students = DB::table('section_students')
			->select('users.id as id','first_name','last_name'
				// DB::raw($raw)
			)
    		->leftJoin('users','student_id','=','users.id')
    		->leftJoin('sections','sections.id','=','users.id')
    		->where('section_id',$request->id)
    		->whereNull('users.deleted_at')
    		->whereNull('sections.deleted_at')
            ->whereNull('section_students.deleted_at')
    		->orderBy('last_name')
    		->get();

    	foreach($students as $student){
            $attendance = StudentAttendance::where('student_id',$student->id)
                ->whereIn('section_attendance_id',$dates->pluck('id'))
                ->get();
            $attendance = $attendance->mapWithKeys(function($item){
                return [$item['section_attendance_id']=>$item['is_present']];
            });
            $student->attendance = $attendance;
    	}
        Log::debug($students);
    
    	$data = new \stdClass();
    	$data->dates = $dates;
    	$data->students = $students;
    	
    	// $data->ids = $date_ids;
    	return response()->json($data);
    }

    public function updateAttendance(Request $request){
    	$request->validate([
    		'id'=>'exists:sections,id',
    		'section_attendance_id'=>'required|exists:section_attendances,id',
    		'student_id' =>'required|exists:users,id',
    		'value'=>'required|required|boolean'
    	]);
    	
    	$attendance = StudentAttendance::where('student_id',$request->student_id)
    	->where('section_attendance_id',$request->section_attendance_id)
    	->first();
    	if($attendance){
    		if($request->value){
    			$attendance->is_present = $request->value;
    			$attendance->save();
                Log::info(sprintf("user %d updated attendance of %d for attendance %d",Auth::user()->id,$request->student_id,$attendance->id));

    			return $attendance;
    		}
    		$attendance->delete();
            Log::info(sprintf("user %d deleted attendance of %d for attendance %d",Auth::user()->id,$request->student_id,$attendance->id));

    		return "record deleted";
    		
    	}
    	if($request->value){
	    	$attendance = new StudentAttendance();
	    	$attendance->student_id = $request->student_id;
	    	$attendance->section_attendance_id=$request->section_attendance_id;
	    	$attendance->is_present = $request->value;
	    	$attendance->save();
            Log::info(sprintf("user %d created attendance of %d for attendance %d",Auth::user()->id,$request->student_id,$attendance->id));
	    	return $attendance;
	    }

    	return 'no update needed';
    }

    public function deleteAttendance(Request $request){
    	$request->validate([
    		'id'=>'exists:sections,id',
    		'section_attendance_id'=>'exists:section_attendances,id'
    	]);
    	// return $request->section_attendance_id;
    	$attendance = SectionAttendance::findOrFail($request->section_attendance_id);
    	$attendance->delete();
        Log::info(sprintf("user %d deleted attendance %d for section %d",Auth::user()->id,$attendance->id,$attendance->section_id));

    	return "attendance deleted";
    }

    public function addAttendance(Request $request){
    	$messages = [
    		'date.unique'=> 'Date already exists for that section'
    	];
    	$section_id = $request->id;
    	$date = $request->date;
    	$date = Carbon::parse($date)->format('Y-m-d');
    	// return $date;
    	
    	$request->validate(
    		[
				'id'=>'exists:sections,id',
				'date' => [
					'required',
					'date_format:Y-m-d',
					Rule::unique('section_attendances')->where(function($query) use($date,$section_id){
						return $query->where('date',$date)
							->where('section_id',$section_id)
							->whereNull('deleted_at');
					})
				]
    		]
    	);

    	$attendance = new SectionAttendance();
    	$attendance->section_id = $section_id;
    	$attendance->date = $date;
    	$attendance->save();
    	
        Log::info(sprintf("user %d added attendance %d for section %d",Auth::user()->id,$attendance->id,$attendance->section_id));
    
    	return $attendance;
    }

    
}
