<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStudentAttendancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_attendances', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('section_attendance_id');
            $table->unsignedBigInteger('student_id');
            $table->boolean('is_present');
            $table->timestamps();
            $table->foreign('section_attendance_id')->references('id')->on('section_attendances');
            $table->foreign('student_id')->references('id')->on('users');
            $table->unique(['section_attendance_id','student_id']);
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_attendances');
    }
}
