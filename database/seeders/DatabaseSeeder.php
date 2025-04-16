<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\User;
use App\Module;
use App\ModuleVersion;
use App\ModuleAssignment;
use App\UserPermission;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeders.
     */
    public function run(): void
    {
        $user1 = new User([
            'unique_id' => 'B00505893',
            'first_name' => 'Tim',
            'last_name' => 'Cortesi',
            'email' => 'tcortesi@binghamton.edu',
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

        $user2 = new User([
            'unique_id' => 'B00450942',
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

        $module = new Module([
            'name' => 'Test Module',
            'description' => 'This is the first test module',
            'owner_user_id' => $user1->id,
            'message_configuration' => (Object)[],
            'assignment_configuration' => (Object)[],
            'templates' => (Object)[
                'assignment'=>"<h3> Hello {{user.first_name}} {{user.last_name}}</h3>
                        <br>
                        <p style='font-size:16px;'>You are assigned to {{module.name}}</p>
                        <br>
                        <p style='font-size:16px;'>Due Date: {{module.due_date}}</p>
                        <br>
                        <p style='font-size:16px;'>Access to Assignment: 
                            <a href='{{link}}'>{{module.name}}</a>
                        </p>",
                'reminder' => '<div class=\'container\'>
                <h3> Hello {{user.first_name}} {{user.last_name}}<h3>
                <br>
                <p style=\'font-size:16px;\'>Your assignment {{module.name}} has a due date soon:
                    <br>
                    Due date {{module.due_date}}
                    <br>
                    Assignment Link: 
                    <a href=\'{{link}}\'>{{module.name}}</a>
                </p>
            </div>',
                'completion_notification'=>"<h3> Hello {{user.first_name}} {{user.last_name}}</h3>
                            <br>
                            <p style='font-size:16px;'>You completed the {{module.name}} course</p>
                            <br>
                            <p style='font-size:16px;'>Certificate Link: 
                                <a href='{{link}}'>Certificate</a>
                            </p>",
                'past_due_reminder'=>"<div class='container'>
                <h3> Hello {{user.first_name}} {{user.last_name}}<h3>
                <br>
                <p style='font-size:16px;'>Your assignment {{module.name}} has a due date soon:
                    <br>
                    Due date {{module.due_date}}
                    <br>
                    Assignment Link: 
                    <a href='{{link}}'>{{module.name}}</a>
                </p>
            </div>"
                ]
        ]);
        $module->save();

        $moduleVersion = new ModuleVersion([
            'name' => 'Test Module',
            'module_id' => $module->id,
            'type' => 'articulate_tincan',
            'reference' => (Object)['filename'=>'story.html'],
        ]);
        $moduleVersion->save();
        $moduleVersion2 = new ModuleVersion([
            'name' => 'Test Module 2 11/18/19',
            'module_id' => $module->id,
            'type' => 'articulate_tincan',
            'reference' => (Object)['filename'=>'story.html'],
        ]);
        $moduleVersion2->save();
        $module->module_version_id = $moduleVersion->id;
        $module->save();

        $moduleAssignment1 = new ModuleAssignment([
            'user_id' => $user1->id,
            'module_version_id' => $moduleVersion->id,
            'module_id' => $moduleVersion->module_id,
            'date_assigned' => now(),
            'date_due' => now()->addDays(30),
            'assigned_by_user_id' => $user1->id,
        ]);
        $moduleAssignment1->save();
        $moduleAssignment2 = new ModuleAssignment([
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

        $userPermission1 = new UserPermission([
            'user_id' => $user1->id,
            'permission' => 'manage_user_permissions',
        ]);
        $userPermission1->save();
        $userPermission2 = new UserPermission([
            'user_id' => $user2->id,
            'permission' => 'manage_user_permissions',
        ]);
        $userPermission2->save();
        $userPermission2 = new UserPermission([
            'user_id' => $user2->id,
            'permission' => 'manage_user_permissions',
        ]);
        $userPermission2->save();
    }
}