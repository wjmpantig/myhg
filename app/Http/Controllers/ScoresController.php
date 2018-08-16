<?php

namespace App\Http\Controllers;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use App\ScoreType;
use App\SectionScore;
use App\StudentScore;
use Carbon\Carbon;
use DB;
use Log;
class ScoresController extends Controller
{
	public function __construct()
    {
        $this->middleware('auth');
    }

    public function score_types(Request $request){
    	if($request->id){
    		 $type = ScoreType::findOrFail($request->id);
    		 $type->name_plural = str_plural($type->name);
    		 return $type;
    	}
    	return ScoreType::all();
    }



    public function scores(Request $request){
    	$request->validate([
    		'id'=>'exists:sections,id',
    		'type_id'=>'exists:score_types,id'
    	]);
    	$type_id = $request->type_id;
    	
    	$scores = SectionScore::where('section_id',$request->id)
    		->where('score_type_id',$type_id)
			->orderBy('date')
			->get();
		
		$raw = "(select group_concat( concat(section_scores.id,':',score) separator ',') from student_scores 
			join section_scores on section_scores.id = student_scores.section_score_id
			where score_type_id = $type_id and student_scores.student_id = users.id and section_scores.deleted_at is null
			
			group by student_id
			order by date) as scores
		";

		$students = DB::table('section_students')
			->select('users.id as id','first_name','last_name',
				DB::raw($raw)
			)
    		->leftJoin('users','student_id','=','users.id')
    		->leftJoin('sections','sections.id','=','users.id')
    		->where('section_id',$request->id)
    		->whereNull('users.deleted_at')
    		->whereNull('sections.deleted_at')
            ->whereNull('section_students.deleted_at')
    		->orderBy('last_name');
    	// Log::debug($students->toSql());
		$students = $students->get();    		
    		

    	foreach($students as $student){
    		
    		$student->scores = $student->scores ?  collect(array_map(function ($score){
    			return explode(':',$score);
    		}, explode(',', $student->scores)))->toAssoc() : [];
    		// $student->scores= array_collapse($student->scores);

    	}

    
    	$data = new \stdClass();
    	$data->scores = $scores;
    	$data->students = $students;
    	
    	// $data->ids = $date_ids;
    	return response()->json($data);
    }

    public function updateStudentScore(Request $request){
		$request->validate([
    		'section_id'=>'exists:sections,id',
    		'type_id'=>'exists:score_types,id',
    		'score_id'=>'exists:sections_scores,id',
    		'student_id'=>'exists:section_students,student_id'
    	]);
    	$highest = SectionScore::findOrFail($request->score_id);
    	if(strlen($request->score)>0){
	    	$request->validate([
	    		'score'=>"numeric|between:0,$highest->total"
	    	]);
	    }
    	$score = StudentScore::where('student_id',$request->student_id)
    			->where('section_score_id',$request->score_id)->first();
    	if(strlen($request->score) == 0){
    		if($score){
    			$score->delete();
    			return "deleted score";
    		}
    		return "nothing to do";
    	
    	}
    	if($score){
    		$score->score = $request->score;
    		$score->save();
    		return $score;
    	}
    	$score = new StudentScore();
    	$score->score = $request->score;
    	$score->student_id = $request->student_id;
    	$score->section_score_id = $request->score_id;
    	$score->save();
    	return $score;
    }
    
    public function updateScore(Request $request){
    	
    	$request->validate([
    		'section_id'=>'exists:sections,id',
    		'type_id'=>'exists:score_types,id',
    		'score_id'=>'exists:sections_scores,id',
    	
    	]);
    	$highest = StudentScore::select(DB::raw('max(score) as highest_score'))
    		->where('section_score_id',$request->score_id)->first();
    	
    	if($highest){
    		$highest = $highest->highest_score;
    	}
    	// Log::debug($highest);
    	$request->validate([
			'total'=>"required|numeric|between:$highest,9999.99"
    	]);
    	$score = SectionScore::where('section_id',$request->section_id)
    		->where('score_type_id',$request->type_id)
    		->where('id',$request->score_id)->first();
    	if(!$score){
    		return response('score doesnt exist',404);
    	}
    	$score->total = $request->total;
    	$score->save();
    	return $score;
    }

    public function addScore(Request $request){
    	$messages = [
    		'date.unique'=> 'Date already exists for that section'
    	];
    	$section_id = $request->section_id;
    	$date = $request->date;
    	$date = Carbon::parse($date)->format('Y-m-d');
    	// return $date;
    	
    	$request->validate(
    		[
				'section_id'=>'exists:sections,id',
    			'type_id'=>'exists:score_types,id',
				'date' => [
					'required',
					'date_format:Y-m-d',
					Rule::unique('section_scores')->where(function($query) use($date,$section_id){
						return $query->where('date',$date)
							->where('section_id',$section_id)
							->whereNull('deleted_at');
					})
				]
    		]
    	);

    	$score = new SectionScore();
    	$score->section_id = $section_id;
    	$score->score_type_id = $request->type_id;
    	$score->date = $date;
    	$score->total = 0;
    	$score->save();
    	
    	

    	return $score;
    }

    public function deleteScore(Request $request){
	 	$request->validate([
			'section_id'=>'exists:sections,id',
			'type_id'=>'exists:score_types,id',
			'score_id'=>'exists:sections_scores,id',
	
    	]);
    	$score = SectionScore::findOrFail($request->score_id);
    	$score->delete();
    	return "total score deleted";
    }
}
