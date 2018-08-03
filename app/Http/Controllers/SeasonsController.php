<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Season;
class SeasonsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function all(){
    	return Season::all();
    }

    public function latest(){
    	return Season::orderBy('created_at','desc')->first();
    }
}
