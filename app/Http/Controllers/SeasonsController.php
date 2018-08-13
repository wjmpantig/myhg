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
    	return Season::orderBy('created_at','desc')
            ->orderBy('id','desc')
            ->get();
    }

    public function latest(){
    	return Season::orderBy('created_at','desc')
            ->orderBy('id','desc')
            ->first();
    }
}
