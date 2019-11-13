<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModuleAssignmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('module_assignments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('user_id')->unique();
            $table->unsignedBigInteger('module_version_id')->index();
            $table->unsignedBigInteger('module_id')->index();

            $table->date('date_assigned');
            $table->date('date_due');
            $table->date('date_started');
            $table->date('date_completed');

            $table->unsignedBigInteger('updated_by_user_id')->index();
            $table->unsignedBigInteger('assigned_by_user_id')->index();
            $table->json('current_state');

//           FOREIGN KEYS
            $table->foreign('module_id')
                ->references('id')
                ->on('modules');

            $table->foreign('module_version_id')
                ->references('id')
                ->on('module_versions');

            $table->foreign('updated_by_user_id')
                ->references('id')
                ->on('users');
            $table->foreign('assigned_by_user_id')
                ->references('id')
                ->on('users');
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
        Schema::dropIfExists('module_assignments');
    }
}
