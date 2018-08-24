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
use App\ExportFile;
use Auth;
use DB;
use Log;
class ExportController extends Controller
{
	private $debug = null;
	// private $spreadsheet = null;
	public function __construct()
    {
        $this->middleware('auth');
    }

    public function get(Request $request){

    	$file = ExportFile::findOrFail($request->id);

		Log::info(sprintf("user %d downloaded file %d",Auth::user()->id,$file->id));
		return response()->file($file->filename);
    }

    public function export(Request $request){
    	$request->validate([
    		'id'=>'required|exists:sections,id',
    		'passing_grade'=>'required|numeric|between:0,100',
    		'pass_finals'=>'required|boolean',
    		'pass_finals_grade'=>'numeric|between:0,100',
    		'criteria.attendance'=>'required|numeric|between:0,100',
    		'criteria.homework'=>'required|numeric|between:0,100',
    		'criteria.quiz'=>'required|numeric|between:0,100',
    		'criteria.final-exam'=>'required|numeric|between:0,100',
    		'criteria.teachers-grade'=>'required|numeric|between:0,100',
    		'criteria'=>[
    			function($attr,$val,$fail){
    				// Log::debug($attr,collect((array)$val));
    				$total = 0;
    				foreach($val as $k=>$v){
    					$total += $v;
    				}
    				if($total != 100){
    					return $fail($attr . ' is invalid');
    				}
    			}
    		]
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
    	$summary_sheet = $spreadsheet->getActiveSheet();
    	$summary_sheet->setTitle('Summary');
    	$this->createAttendanceSheet($spreadsheet,$id,$students);
    	$this->createScoreSheets($spreadsheet,$id,$students);
    	$this->createSummarySheet($summary_sheet,$id,$students,
    		$request->criteria,
    		$request->passing_grade,
    		$request->pass_finals,
    		$request->pass_finals_grade
    	);
    	$spreadsheet->setActiveSheetIndexByName('Summary');
    	$time = Carbon::now();
		$filename = sprintf("%s-%s.xlsx",$section->name,$time->format("Y-m-d-U"));
		// $filename = "test.xlsx";
		// return $filename;
		$filename = storage_path('app/public/'.$filename);
		$file = new ExportFile();
		$file->filename = $filename;
		$file->save();
    	$writer = new Xlsx($spreadsheet);
		$writer->save($filename);
		// $out = is_null($this->debug) ? 'complete' : $this->debug;
		Log::info(sprintf("user %d created file %d",Auth::user()->id,$file->id));
    	return $file;
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
		Log::debug($dates);

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
    	$row = 2;
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
			Log::debug($attendance);
    		foreach($dates as $id=>$date){
    			$val = $attendance->get($id);
    			// if($val){
					$sheet->setCellValueByColumnAndRow($col,$row,$val);
				// }
				$col++;
    		}
    		$formula = sprintf("=SUM(%s:%s)",LookupRef::cellAddress($row,$col-$dates->count(),2,true),LookupRef::cellAddress($row,$col-1,2,true));
    		$sheet->setCellValueByColumnAndRow($col++,$row,$formula);
    		$formula = sprintf("=%s/%s",LookupRef::cellAddress($row,$col-1,2,true),LookupRef::cellAddress($total[1],$total[0],2,true));
    		if(!isset($student->summary)){
    			$student->summary = [];
    		}
    		$student->summary[str_slug('attendance')] = LookupRef::cellAddress($row,$col,1,true,$sheet->getTitle());
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

    		if(!isset($student->summary)){
    			$student->summary = [];
    		}
    		$student->summary[str_slug($type->name)] = LookupRef::cellAddress($row,$col,1,true,$sheet->getTitle());

    		$row++;
    	}

    }

    

    private function createSummarySheet($sheet,$section_id,$students,$criterias,$passing_grade,$pass_finals,$pass_finals_grade){
    	$row = 2;
    	$col = 1;
    	$columns = ['Attendance','Homework','Quiz','Teacher\'s Grade','Final Exam','Final Grade','Remarks'];
    	if($pass_finals){
    		array_splice($columns,5,0,['Passed Final Exam']);
    	}
    	$passing_grade_cell = null;
    	$pass_final_cell = null;
    	$sheet->setCellValueByColumnAndRow($col++,$row,"Name");
        foreach($columns as $column){
        	$slug = str_slug($column);
			$percent = isset($criterias[$slug]);
			if($percent){
				$percent = $criterias[$slug];
				$criterias[$slug] = [
					'value'=>$percent,
					'ref'=>LookupRef::cellAddress($row-1,$col,4,true)
				];
				$sheet->setCellValueByColumnAndRow($col,$row-1,$percent/100);
				$sheet->getStyleByColumnAndRow($col,$row-1)
	    			->getNumberFormat()
	    			->setFormatCode(NumberFormat::FORMAT_PERCENTAGE);
			}else if(strcmp(str_slug($column),str_slug('Final Grade'))==0){
				$sheet->setCellValueByColumnAndRow($col,$row-1,$passing_grade/100);
				$passing_grade_cell = LookupRef::cellAddress($row-1,$col,4,true);
				$sheet->getStyleByColumnAndRow($col,$row-1)
	    			->getNumberFormat()
	    			->setFormatCode(NumberFormat::FORMAT_PERCENTAGE);
			}else if(strcmp(str_slug($column),str_slug('Passed Final Exam'))==0){
				$sheet->setCellValueByColumnAndRow($col,$row-1,$pass_finals_grade/100);
				$pass_final_cell = LookupRef::cellAddress($row-1,$col,4,true);
				$sheet->getStyleByColumnAndRow($col,$row-1)
	    			->getNumberFormat()
	    			->setFormatCode(NumberFormat::FORMAT_PERCENTAGE);
			}
        	$sheet->setCellValueByColumnAndRow($col++,$row,$column);
        }
        $row++;
    	foreach($students as $student){
    		$col=1;
    		$sheet->setCellValueByColumnAndRow($col++,$row,$student->name);
    		foreach($columns as $column){
    			$slug = str_slug($column);
    			
    			if(strcmp($slug,str_slug('Teacher\'s Grade') )== 0){
			        $sheet->setCellValueByColumnAndRow($col,$row,0);
			        $sheet->getStyleByColumnAndRow($col,$row)
    					->getNumberFormat()
    					->setFormatCode(NumberFormat::FORMAT_PERCENTAGE);

    			}else if(strcmp($slug,str_slug('Passed Final Exam') )== 0){
    				$formula = sprintf('=IF(%s>=%s,"PASSED","FAIL")',$student->summary_sheet_cells[str_slug('Final Exam')],$pass_final_cell);
			        $sheet->setCellValueByColumnAndRow($col,$row,$formula);

    			}else if(strcmp($slug,str_slug('Final Grade')) == 0){
    				$formula = '=';
    				$count = 0;
    				foreach($criterias as $criteria=>$val){
    					if($count >0){
    						$formula .='+';
    					}
    					$formula .= sprintf("(%s*%s)",$student->summary_sheet_cells[$criteria],$val['ref']);
    					$count++;
    				}
		    		// Log::debug($formula);
			        $sheet->setCellValueByColumnAndRow($col,$row,$formula);
			        $sheet->getStyleByColumnAndRow($col,$row)
	    				->getNumberFormat()
	    				->setFormatCode(NumberFormat::FORMAT_PERCENTAGE);
    			}else if(strcmp($slug,str_slug('Remarks')) == 0){
    				$grade_pass_formula = sprintf('%s>=%s',LookupRef::cellAddress($row,$col-1,1,true),$passing_grade_cell);
    				if($pass_finals){
    					$formula = sprintf('=IF(OR(%s="PASSED",%s),"PASSED","FAIL")',$student->summary_sheet_cells[str_slug('Passed Final Exam')],$grade_pass_formula);
    				}else{
	    				$formula = sprintf('=IF(%s,"PASSED","FAIL")',$grade_pass_formula) ;
	    			}
		    			// Log::debug($cell);
		        	$sheet->setCellValueByColumnAndRow($col,$row,$formula);
    			}else{
    				$cell = isset($student->summary[$slug]) ? $student->summary[$slug] : null;
	    			if($cell){
		    			$formula = sprintf("=%s",$cell);
		    			// Log::debug($cell);
			        	$sheet->setCellValueByColumnAndRow($col,$row,$formula);
			        	$sheet->getStyleByColumnAndRow($col,$row)
	    					->getNumberFormat()
	    					->setFormatCode(NumberFormat::FORMAT_PERCENTAGE);
			        }
    			}
    			if(!isset($student->summary_sheet_cells)){
	    			$student->summary_sheet_cells = [];
	    		}
	    		$student->summary_sheet_cells[$slug] = LookupRef::cellAddress($row,$col,1,true);
    			
    			$col++;
	        }
    		$row++;

    	}
    }
}
