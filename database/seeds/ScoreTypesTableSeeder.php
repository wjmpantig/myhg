<?php

use Illuminate\Database\Seeder;
use App\ScoreType;
class ScoreTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $types = ['Homework','Quiz','Final Exam'];
        foreach($types as $type){
        	$t = new ScoreType();
        	$t->name = $type;
        	$t->save();
        }
    }
}
