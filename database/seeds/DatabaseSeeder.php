<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
        	UserTypesTableSeeder::class,
        	SeasonsTableSeeder::class,
        	SectionsTableSeeder::class,
        	UsersTableSeeder::class,
        	// SectionAttendancesTableSeeder::class,
        	ScoreTypesTableSeeder::class
        ]);
        
    }
}
