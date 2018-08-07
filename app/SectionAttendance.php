<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SectionAttendance extends Model
{
	use SoftDeletes;
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'date'
    ];

    public function attendance(){
        return $this->hasMany('App\StudentAttendance');
    }

    public function section(){
    	return $this->belongsTo('App\Section');
    }

    

}
