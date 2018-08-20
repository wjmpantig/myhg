<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\User;
use Auth;
use Log;
class UsersController extends Controller
{
    public function update_password(Request $request){
    	$request->validate([
    		'current_password'=>'required',
    		'password'=>'required|confirmed|min:8'
    	]);
    	$current_user = Auth::user();
    	$user = User::where('id',$current_user->id)
    		->first()
    		->makeVisible('password');
    	if(!Hash::check($request->current_password,$user->password)){
    		return response(['errors'=>[
    				'current_password'=>'Invalid password'
    			]],500);
    	}
    	$user->password = Hash::make($request->current_password);
    	$user->save();
    	return "success";
    }

    public function create(Request $request){
    	$request->validate([
    		'last_name'=>'required',
    		'first_name'=>'required',
    		'email'=>'required|email|unique:users,email',
    		'password'=>'required|confirmed|min:8',
    		'type'=>'required|exists:user_types,id'
    	]);
    	$user = new User();
    	$user->last_name = $request->last_name;
    	$user->first_name = $request->first_name;
    	$user->email = $request->email;
    	$user->password = Hash::make($request->password);
    	$user->user_type_id = $request->type;
    	$user->save();
        Log::info(sprintf("user %d created user %d",Auth::user()->id,$user->id));

    	return $user;
    }
}
