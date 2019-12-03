<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->longText('description')->nullable()->default(null);
            $table->unsignedBigInteger('owner_user_id')->nullable()->default(null);
            $table->json('message_configuration')->nullable()->default(null);
            $table->json('assignment_configuration')->nullable()->default(null);
            $table->unsignedBigInteger('module_version_id')->nullable()->default(null)->index();
            $table->timestamps();
            $table->foreign('owner_user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('modules');
    }
}
