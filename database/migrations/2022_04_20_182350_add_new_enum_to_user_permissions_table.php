<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewEnumToUserPermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE user_permissions MODIFY `permission` ENUM(  
        'manage_user_permissions',
        'manage_groups',
        'manage_users',
        'impersonate_users',
        'manage_modules',
        'assign_modules',
        'manage_reports',
        'run_reports',
        'manage_bulk_assignments',
        'manage_workshops',
        'assign_workshops'
        ) ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE user_permissions MODIFY `permission` ENUM(  
        'manage_user_permissions',
        'manage_groups',
        'manage_users',
        'impersonate_users',
        'manage_modules',
        'assign_modules',
        'manage_reports',
        'run_reports',
        'manage_bulk_assignments')");
    }
}