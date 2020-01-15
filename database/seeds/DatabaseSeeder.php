<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $user1 = new App\User([
            'unique_id' => '_demo_tim',
            'first_name' => 'Tim',
            'last_name' => 'Cortesi',
            'email' => 'atanriv1@binghamton.edu',
            'title'=>'Assistant Director',
            'payroll_code'=>12342123,
            'supervisor'=>'Michael Allington',
            'department_id'=>'123',
            'department_name'=>'ITS',
            'division_id'=>'2',
            'division'=>'ITS',
            'negotiation_unit'=>'90'
        ]);
        $user1->save();

        $user2 = new App\User([
            'unique_id' => '_demo_ali',
            'first_name' => 'Ali',
            'last_name' => 'Tanriverdi',
            'email' => 'atanrive@binghamton.edu',
            'title'=>'Software Developer',
            'payroll_code'=>12342123,
            'supervisor'=>'Tim Cortesi',
            'department_id'=>'123',
            'department_name'=>'ITS',
            'division_id'=>'2',
            'division'=>'ITS',
            'negotiation_unit'=>'90'
        ]);
        $user2->save();

        $module = new App\Module([
            'name' => 'Test Module',
            'description' => 'This is the first test module',
            'owner_user_id' => $user1->id,
            'message_configuration' => (Object)[],
            'assignment_configuration' => (Object)[],
        ]);
        $module->save();

        $moduleVersion = new App\ModuleVersion([
            'name' => 'Test Module',
            'module_id' => $module->id,
            'type' => 'articulate_tincan',
            'reference' => (Object)['filename'=>'story.html'],
        ]);
        $moduleVersion->save();
        $moduleVersion2 = new App\ModuleVersion([
            'name' => 'Test Module 2 11/18/19',
            'module_id' => $module->id,
            'type' => 'articulate_tincan',
            'reference' => (Object)['filename'=>'story.html'],
        ]);
        $moduleVersion2->save();


        $moduleAssignment1 = new App\ModuleAssignment([
            'user_id' => $user1->id,
            'module_version_id' => $moduleVersion->id,
            'module_id' => $moduleVersion->module_id,
            'date_assigned' => now(),
            'date_due' => now()->addDays(30),
            'assigned_by_user_id' => $user1->id,
        ]);
        $moduleAssignment1->save();
        $moduleAssignment2 = new App\ModuleAssignment([
            'user_id' => $user2->id,
            'module_version_id' => $moduleVersion->id,
            'module_id' => $moduleVersion->module_id,
            'date_assigned' => now(),
            'date_due' => now()->addDays(30),
            'assigned_by_user_id' => $user1->id,
        ]);
        $moduleAssignment2->save();
//        $moduleAssignment3 = new App\ModuleAssignment([
//            'user_id' => $user1->id,
//            'module_version_id' => $moduleVersion2->id,
//            'module_id' => $moduleVersion2->module_id,
//            'date_assigned' => now(),
//            'date_due' => now()->addDays(30),
//            'assigned_by_user_id' => $user1->id,
//        ]);
//        $moduleAssignment3->save();

        $userPermission1 = new App\UserPermission([
            'user_id' => $user1->id,
            'permission' => 'manage_user_permissions',
        ]);
        $userPermission1->save();
        $userPermission2 = new App\UserPermission([
            'user_id' => $user2->id,
            'permission' => 'manage_user_permissions',
        ]);
        $userPermission2->save();
        $userPermission2 = new App\UserPermission([
            'user_id' => $user2->id,
            'permission' => 'manage_user_permissions',
        ]);
        $userPermission2->save();
    }
}