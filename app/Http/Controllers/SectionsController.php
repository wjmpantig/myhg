<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Section;
use App\Season;
class SectionsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function all(Request $request){
    	$season = empty($request->season_id) ? Season::orderBy('created_at','desc')->first() : Season::findOrFail($request->season_id);
		$sections = Section::where('season_id',$season->id)->get();
		return $sections;
    }

    public function update(Request $request){
    	$request->validate([
    		'id'=>'exists:sections,id',
    		'name'=>'required|max:20'
    	]);
    	$section = Section::findOrFail($request->id);
    	$section->name = $request->name;
    	$section->save();
    	return $section;
    }

    public function delete(Request $request){
    	$request->validate([
    		'id'=>'exists:sections,id'
    	]);
    	$section = Section::findOrFail($request->id);
    	$section->delete();
    	return "Delete success";
    }

    public function get(Request $request){
    	$request->validate([
    		'id'=>'exists:sections,id'
    	]);
    	$section = Section::findOrFail($request->id);
    	return $section;
    }


    public function students(Request $request){
    	$request->validate([
    		'id'=>'exists:sections,id'
    	]);
    	$section = Section::findOrFail($request->id);
    	
    }
}
