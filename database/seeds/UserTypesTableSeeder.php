<?php

use Illuminate\Database\Seeder;
use App\UserType;
class UserTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	$names = ['Administrator','Teacher','Student'];
    	foreach($names as $name){
    		$type = new UserType();
    		$type->name = $name;
    		$type->save();
    	}
    }
}
