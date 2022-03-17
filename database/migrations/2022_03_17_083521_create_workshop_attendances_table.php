<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkshopAttendancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('workshop_attendances', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreign('workshop_offering_id')->references('id')->on('workshop_offerings');
            $table->foreign('workshop_id')->references('id')->on('workshops');
            $table->foreign('user_id')->references('id')->on('users');
            $table->enum('status',['assigned','attended','in_progress','passed','failed','completed'])->default('assigned');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('workshop_attendances');
    }
}
