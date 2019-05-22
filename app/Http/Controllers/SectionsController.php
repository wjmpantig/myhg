<?php

namespace App\Http\Controllers;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Section;
use App\SectionAttendance;
use App\SectionStudent;
use App\StudentAttendance;
use App\Season;
use App\ScoreType;
use App\SectionScore;
use App\User;
use App\UserType;
use Carbon\Carbon;
use League\Csv\Reader;
use League\Csv\Statement;
use Faker\Factory;
use Auth;
use DB;
use Log;
use Hash;

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
	
	public function create(Request $request){
		$request->validate([
			'name'=>'required',
			'season_id'=>'required|exists:seasons,id'
		]);
		$section = new Section();
		$section->season_id = $request->season_id;
		$section->name = $request->name;
		$section->save();
		return $section;
	}

    public function update(Request $request){
    	$request->validate([
    		'id'=>'required|exists:sections,id',
			'name'=>[
				'required',
				'max:20',
				Rule::unique('sections')->where(function($query) use($request){
					$s = Section::findOrFail($request->id);
					return $query->where('id','<>',$request->id)
						->where('season_id',$s->season_id)
                        ->where('name',$request->name)
                        ->whereNull('deleted_at');
                }),
			]
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
            ->whereNull('users.deleted_at')
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
        // Log::debug($dates);
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
                return [$item['section_attendance_id']=>$var = filter_var($item['is_present'], FILTER_VALIDATE_BOOLEAN)];
            });
            $student->attendance = $attendance;
    	}
        // Log::debug($students);
    
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
					// Rule::unique('section_attendances')->where(function($query) use($date,$section_id){
					// 	return $query->where('date',$date)
					// 		->where('section_id',$section_id)
					// 		->whereNull('deleted_at');
					// })
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
	
	public function importFile(Request $request){
		$request->validate([
			'id'=>'exists:sections,id',
			'file'=>'required|file|mimetypes:application/vnd.ms-excel,text/plain,text/csv,text/tsv'
		]);
		$id = $request->id;
		$file = $request->file('file');
		// return print_r($file->getPathName(),true);
    	$student_type = UserType::where('name','Student')->first();

		$result = $this->importUsersFromCSV($file->getPathName(),$student_type->id,$id);
		return $result;
	}

	private function importUsersFromCSV($path,$user_type_id,$section_id=null){
    	$csv = Reader::createFromPath($path,'r');
		$csv->setHeaderOffset(0);
		$stmt = new Statement();

		$records = $stmt->process($csv);
		$rows = 0;
		$total = 0;
		foreach($records as $offset => $record){
			$rows++;
			$validator = Validator::make($record,[
	    		// 'email'=>'email|unique:users,email',
	    		'first_name'=>'required',
	    		'last_name'=>'required'
	    	]);
            // Log::debug($record);
			// if($validator->fails()){
			// 	// Log::debug($validator->errors());
            //     if($validator->errors()->has('email')){
            //         // Log::debug('generating fake email..');
            //         // Log::debug("fake email generated: $email");
            //     }else{
				// 		continuess;
				//     }
				// }
			$email = $this->generateRandomEmail($record['first_name'],$record['last_name'],Factory::create());
			$record['email'] =  $email;
			$user = new User();
			$user->first_name = title_case($record['first_name']);
			$user->last_name = title_case($record['last_name']);
			$user->password = Hash::make(str_random(20));
			$user->email = $record['email'];
			$user->user_type_id = $user_type_id;
			$user->save();
            if($section_id){
    			$section_student = new SectionStudent();
    			$section_student->student_id = $user->id;
    			$section_student->section_id = $section_id;
    			$section_student->save();
			}
			$total++; 
		}
		return [
			'total'=>$total,
			'rows'=>$rows
		];
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
