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
            $table->string('user_id');
            $table->unsignedBigInteger('module_version_id')->index();
            $table->unsignedBigInteger('module_id')->index();
            $table->timestamp('date_assigned')->nullable()->default(null);
            $table->timestamp('date_due')->nullable()->default(null);
            $table->timestamp('date_started')->nullable()->default(null);
            $table->timestamp('date_completed')->nullable()->default(null);
            $table->unsignedBigInteger('updated_by_user_id')->nullable()->default(null);
            $table->unsignedBigInteger('assigned_by_user_id')->nullable()->default(null);
            $table->json('current_state')->nullable()->default(null);
            $table->unique(['user_id','module_version_id']);

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
