<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkshopReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('workshop_reports', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->default('');
            $table->longText('description')->nullable()->default(null);
            $table->json('report')->nullable()->default(null);
            $table->unsignedBigInteger('owner_user_id')->nullable()->default(null);
            $table->timestamps();
            $table->foreign('owner_user_id')->references('id')->on('users');
            $table->json('permissions')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('workshop_reports');
    }
}
