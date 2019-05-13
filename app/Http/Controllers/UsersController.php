<?php

namespace App\Http\Controllers;

use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\User;
use App\UserType;
use Auth;
use Log;
use DB;
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
		Log::info(sprintf("user %d updated password",Auth::user()->id));		 
    	return "success";
	 }
	 
	 public function all(){
		 $student_type = UserType::where('name','Student')->first();

		 return User::select('users.id',DB::raw('concat(last_name,", ",first_name) as name'))
			 ->where('user_type_id','<>',$student_type->id)
			 ->orderBy('last_name','asc')
			 ->get();
	 }

	 public function get(Request $request){
		 $request->validate([
			 'id'=>'exists:users,id'
		 ]);
		 return User::findOrFail($request->id);
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
	 public function update(Request $request){
		$request->validate([
			'last_name'=>'required',
			'first_name'=>'required',
			'email'=>[
					'required',
					'email',
					Rule::unique('users')->where(function($query) use($request){
						return $query->where('id','<>',$request->id)
							 ->where('email',$request->email)
							 ->whereNull('deleted_at');
				  }),
				],
			'password'=>'sometimes|min:8',
		]);
		$user = User::findOrFail($request->id);
		$user->last_name = $request->last_name;
		$user->first_name = $request->first_name;
		$user->email = $request->email;
		if(!empty($request->password)){
			$user->password = Hash::make($request->password);
		}
		$user->save();
		return $user;
	 }
}
