<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkshopsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('workshops', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->longText('description')->nullable()->default(null);
            $table->string('icon')->nullable()->default(null);
            //Owner type?
            $table->foreign('owner_id')->references('id')->on('users');
            $table->json('config')->nullable()->default(null);
            $table->json('files')->nullable()->default(null);
            $table->unsignedInteger('duration')->default(0);
            $table->boolean('public')->nullable(false)->default(false);
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
        Schema::dropIfExists('workshops');
    }
}
