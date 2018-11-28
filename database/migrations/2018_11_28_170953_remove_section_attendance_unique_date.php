<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveSectionAttendanceUniqueDate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('section_attendances', function (Blueprint $table) {
            $table->dropForeign('section_attendances_section_id_foreign');
            $table->dropUnique(['section_id','date']);
            $table->foreign('section_id')->references('id')->on('sections');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('section_attendances', function (Blueprint $table) {
            $table->unique(['section_id','date']);
            // $table->foreign('section_id')->references('id')->on('sections');
        });
    }
}
