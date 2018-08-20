<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\UserType;
class UserTypesController extends Controller
{
    public function all(){
    	return UserType::all();
    }
}
