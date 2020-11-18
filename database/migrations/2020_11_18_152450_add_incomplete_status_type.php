<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIncompleteStatusType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        DB::statement("ALTER TABLE module_assignments MODIFY status ENUM('assigned','attended','in_progress','passed','failed','completed','incomplete') NOT NULL DEFAULT 'assigned'");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        DB::statement("ALTER TABLE module_assignments MODIFY status ENUM('assigned','attended','in_progress','passed','failed','completed') NOT NULL DEFAULT 'assigned'");
    }
}
