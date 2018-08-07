<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSectionScoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('section_scores', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedTinyInteger('score_type_id');
            $table->unsignedBigInteger('section_id');
            $table->unsignedDecimal('total',6,2);
            $table->date('date');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('score_type_id')->references('id')->on('score_types');
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
        Schema::dropIfExists('section_scores');
    }
}
