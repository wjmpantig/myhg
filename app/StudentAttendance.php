<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StudentAttendance extends Model
{
	protected $dates = [
        'created_at',
        'updated_at'
    ];
    
    public function section(){
    	return $this->belongsTo('App\SectionAttendance');
    }
    public function user(){
    	return $this->belongsTo('App\User','student_id');
    }
}
