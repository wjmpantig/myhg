<?php

use Illuminate\Database\Seeder;
use App\User_Type;
use App\User;
class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = new User();
        $admin->first_name = "Admin";
        $admin->last_name="Not admin";
        $admin->email="hello@winfred.work";
        $admin->password=bcrypt("fiddle");
        $admin->save();
    }
}
