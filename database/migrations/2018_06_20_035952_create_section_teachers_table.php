<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSectionTeachersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::create('section_teachers', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('section_id');
            $table->unsignedBigInteger('teacher_id');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['section_id','teacher_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('section_teachers');
    }
}
