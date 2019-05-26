<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\UserType;
class UserTypesController extends Controller
{
    public function __construct(){
   	    $this->middleware('auth');
    }
    
    public function all(){
    	return UserType::all();
    }
}
