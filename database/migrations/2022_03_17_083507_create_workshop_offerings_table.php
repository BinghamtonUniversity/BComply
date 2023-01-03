<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkshopOfferingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('workshop_offerings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('workshop_id')->nullable()->default(null);
            $table->unsignedBigInteger('instructor_id')->nullable()->default(null);
            $table->foreign('workshop_id')->references('id')->on('workshops');
            $table->unsignedBigInteger('max_capacity')->default(0);
            $table->string('locations');
            //instructor tpye?
            $table->foreign('instructor_id')->references('id')->on('users');
            $table->timestamp('workshop_date')->nullable()->default(null);
            $table->enum('type',['online','in-person'])->default('online');
            $table->enum('status',['active','cancelled','reactive'])->default('active');
            $table->boolean('is_multi_day')->nullable(false)->default(false);
            $table->json('multi_days')->nullable(true)->default(null);
            $table->softDeletes();
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
        Schema::dropIfExists('workshop_offerings');
    }
}
