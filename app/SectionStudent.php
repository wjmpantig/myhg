<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SectionStudent extends Model
{
	use SoftDeletes;
	
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

   	public function section(){
   		return $this->belongsTo('App\Section');
   	}
   	public function student(){
   		return $this->belongsTo('App\Student');
   	}
}
