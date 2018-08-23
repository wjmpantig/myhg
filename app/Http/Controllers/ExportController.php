<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Calculation\LookupRef;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Carbon\Carbon;
use App\Section;
use App\SectionAttendance;
use App\SectionScore;
use App\StudentAttendance;
use App\StudentScore;
use App\ScoreType;
use DB;
use Log;
class ExportController extends Controller
{
	private $debug = null;
	// private $spreadsheet = null;
	public function __construct()
    {
        // $this->middleware('auth');
    }

    public function export(Request $request){
    	$request->validate([
    		'id'=>'required|exists:sections,id'
    	]);
    	$id = $request->id;
    	$section = Section::findOrFail($id);

    	$students = DB::table('section_students')
			->select('users.id as id',DB::raw('CONCAT(last_name,", ",first_name) as name'))
    		->leftJoin('users','student_id','=','users.id')
    		->leftJoin('sections','sections.id','=','users.id')
    		->where('section_id',$id)
    		->whereNull('users.deleted_at')
    		->whereNull('sections.deleted_at')
            ->whereNull('section_students.deleted_at')
    		->orderBy('last_name')
    		->get();

    	$spreadsheet = new Spreadsheet();
    	$this->createAttendanceSheet($spreadsheet,$id,$students);
    	$this->createScoreSheets($spreadsheet,$id,$students);
    	
    	$time = Carbon::now();
		$filename = sprintf("%s-%s.xlsx",$section->name,$time->format("Y-m-d-U"));
		$filename = "test.xlsx";
		// return $filename;
		
    	$writer = new Xlsx($spreadsheet);
		$writer->save(storage_path('app/public/'.$filename));
		$out = is_null($this->debug) ? 'complete' : $this->debug;
    	return $out;
    }

    private function createAttendanceSheet($spreadsheet,$section_id,$students){
    	$sheet_name = "Attendance";
    	$sheet = new Worksheet($spreadsheet,$sheet_name);

    	$spreadsheet->addSheet($sheet);
		$spreadsheet->setActiveSheetIndexByName($sheet_name);

    	$dates = SectionAttendance::where('section_id',$section_id)
			->orderBy('date')
			->get();
		$dates = $dates->mapWithKeys(function($item){
				return [$item['id']=>Carbon::parse($item['date'])];
		});


    	foreach($students as $student){
            $attendance = StudentAttendance::where('student_id',$student->id)
                ->whereIn('section_attendance_id',$dates->keys())
                ->get();
            $attendance = $attendance->mapWithKeys(function($item){
                return [$item['section_attendance_id']=>$item['is_present']];
            });
            $student->attendance = $attendance;
    	}
		// $this->debug = $students;
    	$row = 1;
    	$col = 1;

    	$sheet->setCellValueByColumnAndRow($col++,$row,"Name");
    	foreach($dates as $id=>$date){
    		$formula = sprintf('=DATEVALUE("%s")',$date);
    		$sheet->setCellValueByColumnAndRow($col++,$row,$formula);
    		$sheet->getStyleByColumnAndRow($col-1,$row)
    			->getNumberFormat()
    			->setFormatCode(NumberFormat::FORMAT_DATE_XLSX14);

    			// $sheet->setCellValueByColumnAndRow($col-1,$row-1,$id); // id
    	}
    	$formula = sprintf("=COUNT(%s:%s)",LookupRef::cellAddress($row,$col-$dates->count(),2,true),LookupRef::cellAddress($row,$col-1,2,true));
    	$sheet->setCellValueByColumnAndRow($col++,$row,"Total");
    	$sheet->setCellValueByColumnAndRow($col,$row,$formula);
    	$total= [$col,$row];
    	$row++;

    	foreach($students as $student){
    		$col=1;
			$sheet->setCellValueByColumnAndRow($col++,$row,$student->name);
			$attendance = $student->attendance;
    		foreach($dates as $id=>$date){
    			$val = $attendance->get($id);
    			if($val){
					$sheet->setCellValueByColumnAndRow($col,$row,$val);
				}
				$col++;
    		}
    		$formula = sprintf("=SUM(%s:%s)",LookupRef::cellAddress($row,$col-$dates->count(),2,true),LookupRef::cellAddress($row,$col-1,2,true));
    		$sheet->setCellValueByColumnAndRow($col++,$row,$formula);
    		$formula = sprintf("=%s/%s",LookupRef::cellAddress($row,$col-1,2,true),LookupRef::cellAddress($total[1],$total[0],2,true));
    		$sheet->setCellValueByColumnAndRow($col,$row,$formula);
    		$sheet->getStyleByColumnAndRow($col,$row)
    			->getNumberFormat()
    			->setFormatCode(NumberFormat::FORMAT_PERCENTAGE);
    		$row++;
    	}
    	return $sheet;
    }

    private function createScoreSheets($spreadsheet,$section_id,$students){
    	$types = ScoreType::all();
    	foreach($types as $type){
    		$sheet = new Worksheet($spreadsheet,$type->name);
    		$spreadsheet->addSheet($sheet);
    		$spreadsheet->setActiveSheetIndexByName($type->name);
    		$this->createScoreSheet($sheet,$type,$section_id,$students);
    	}
    }

    private function createScoreSheet($sheet,$type,$section_id,$students){
    	$scores = SectionScore::where('section_id',$section_id)
    		->where('score_type_id',$type->id)
			->orderBy('date')
			->get();
		$scores = $scores->mapWithKeys(function($item){
				return [
					$item['id']=>[
						'date'=>$item['date'],
						'total'=>$item['total']
			]];
		});
	
    	$this->debug = $students;
		foreach($students as $student){
            $student_scores = StudentScore::where('student_id',$student->id)
                ->whereIn('section_score_id',$scores->keys())
                ->get();
            $student_scores = $student_scores->mapWithKeys(function($item){
                    return [$item['section_score_id']=>$item['score']];
                }
            );
            $student->scores = $student_scores;
    	}
    	$row = 2;
    	$col = 1;
    	$sheet->setCellValueByColumnAndRow($col,$row-1,"Total");
    	$sheet->setCellValueByColumnAndRow($col++,$row,"Name");
    	foreach($scores as $id=>$score){
    			$sheet->setCellValueByColumnAndRow($col++,$row,$score['date']);
    			$sheet->getStyleByColumnAndRow($col-1,$row)
    			->getNumberFormat()
    			->setFormatCode(NumberFormat::FORMAT_DATE_XLSX14);
    			$sheet->setCellValueByColumnAndRow($col-1,$row-1,$score['total']); 

    	}
    	$sheet->setCellValueByColumnAndRow($col,$row,"Total");
    	$formula = sprintf("=SUM(%s:%s)",LookupRef::cellAddress($row-1,$col-$scores->count(),2,true),LookupRef::cellAddress($row-1,$col-1,2,true));
    	$sheet->setCellValueByColumnAndRow($col,$row-1,$formula);
    	$total= [$col,$row-1];
    	$row++;

    	foreach($students as $student){
    		$col=1;
			$sheet->setCellValueByColumnAndRow($col++,$row,$student->name);
			$student_scores = $student->scores;


    		foreach($scores as $id=>$date){
    			$val = $student_scores->get($id);
    			if($val){
					$sheet->setCellValueByColumnAndRow($col,$row,$val);
				}
				$col++;
    		}
		

    		
    		if($scores->count() == 1){
    			$formula = sprintf("=%s/%s",LookupRef::cellAddress($row,$col-1,2,true),LookupRef::cellAddress($total[1],$total[0],2,true));
	    		$sheet->setCellValueByColumnAndRow($col,$row,$formula);
	    		$sheet->getStyleByColumnAndRow($col,$row)
	    			->getNumberFormat()
	    			->setFormatCode(NumberFormat::FORMAT_PERCENTAGE);
    		}else{
    			$formula = sprintf("=SUM(%s:%s)",LookupRef::cellAddress($row,$col-$scores->count(),2,true),LookupRef::cellAddress($row,$col-1,2,true));
    			$sheet->setCellValueByColumnAndRow($col++,$row,$formula);
	    		$formula = sprintf("=%s/%s",LookupRef::cellAddress($row,$col-1,2,true),LookupRef::cellAddress($total[1],$total[0],2,true));
	    		$sheet->setCellValueByColumnAndRow($col,$row,$formula);
	    		$sheet->getStyleByColumnAndRow($col,$row)
	    			->getNumberFormat()
	    			->setFormatCode(NumberFormat::FORMAT_PERCENTAGE);

    		}
    		$row++;
    	}

    }

    private function createTeacherGradeSheet($spreadsheet,$section_id,$students){
    	$sheet_name = "Teacher's Grade";
    	$sheet = new Worksheet($spreadsheet,$sheet_name);

    	$spreadsheet->addSheet($sheet);
		$spreadsheet->setActiveSheetIndexByName($sheet_name);
		$row = 2;
    	$col = 1;
    	$sheet->setCellValueByColumnAndRow($col,$row-1,"Total");
    	$sheet->setCellValueByColumnAndRow($col++,$row,"Name");
    }
}
