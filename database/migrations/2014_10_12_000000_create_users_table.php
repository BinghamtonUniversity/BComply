<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
//            'p_code','supervisor','department','division','title','preferred_name
            $table->bigIncrements('id');
            $table->string('unique_id')->nullable()->unique()->index();
            $table->string('first_name')->nullable()->default(null);
            $table->string('last_name')->nullable()->default(null);
            $table->string('email')->unique()->nullable()->default(null);
            $table->string('p_code')->nullable();
            $table->string('supervisor')->nullable();
            $table->string('department')->nullable();
            $table->string('division')->nullable();
            $table->string('title')->nullable();
            $table->boolean('status')->nullable(false)->default(false);
            $table->string('preferred_name')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->json('params')->nullable()->default(null);
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
