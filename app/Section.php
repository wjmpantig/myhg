<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
   public function season(){
   	return $this->belongsTo('App\Season');
   }
}
