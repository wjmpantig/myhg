<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStudentScoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_scores', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('section_scores_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedDecimal('score',4,2);
            $table->timestamps();
            $table->foreign('section_scores_id')->references('id')->on('section_scores');
            $table->foreign('student_id')->references('id')->on('users');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_scores');
    }
}
