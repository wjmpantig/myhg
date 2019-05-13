<?php

namespace App\Http\Controllers;

use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use App\Season;
use Auth;
use Log;
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

    public function create(Request $request){
        $request->validate([
            'name'=>'required|unique:seasons,name'
        ]);
        $season = new Season();
        $season->name = $request->name;
        $season->save();
        Log::info(sprintf("user %d created season %d",Auth::user()->id,$request->id));
        return $season;
    }

    public function update(Request $request){
        $request->validate([
            'id'=>'required|exists:seasons,id',
            'name'=>[
                'required',
                Rule::unique('seasons')->where(function($query) use($request){
                    return $query->where('id','<>',$request->id)
                        ->where('name',$request->name)
                        ->whereNull('deleted_at');
                }),
            ]
        ]);
        $season = Season::findOrFail($request->id);
        $season->name = $request->name;
        $season->save();
        Log::info(sprintf("user %d updated season %d",Auth::user()->id,$request->id));
        return $season;
    }

    public function delete(Request $request){
        $request->validate([
            'id'=>'exists:seasons,id'
        ]);
        $season = Season::findOrFail($request->id);
        $season->delete();
        Log::info(sprintf("user %d deleted season %d",Auth::user()->id,$request->id));        
        return "Delete success";
    }
}
