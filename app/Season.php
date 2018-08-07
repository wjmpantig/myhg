<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Season extends Model
{
    use SoftDeletes;

	protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function sections(){
    	return $this->hasMany('App\Section');
    }
}
