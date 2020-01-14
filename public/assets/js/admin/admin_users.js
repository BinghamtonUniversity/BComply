gform.options = {autoFocus:false};
user_form_attributes = [
    {type:"hidden", name:"id"},
    {type:"checkbox", name:"active", label:"Active", value:true},
    {type:"text", name:"unique_id", label:"Unique ID"},
    {type:"text", name:"first_name", label:"First Name"},
    {type:"text", name:"last_name", label:"Last Name"},
    {type:"email", name:"email", label:"Email"},
    {type:"text", name:"payroll_code", label:"Payroll Code"},
    {type:"text", name:"supervisor", label:"Supervisor"},
    {type:"text", name:"department_id", label:"Department ID"},
    {type:"text", name:"department_name", label:"Department Name"},
    {type:"text", name:"division_id", label:"Division ID"},
    {type:"text", name:"division", label:"Division Name"},
    {type:"text", name:"title", label:"Title"},
];

$('#adminDataGrid').html(`
<div class="row">
    <div class="col-sm-3 actions">
        <div class="row">
            <div class="col-sm-12 user-search"></div>
        </div>
        <hr>
        <div class="row">
            <div class="col-sm-12">
                <div class="btn btn-success user-new">Create New User</div>
            </div>
        </div>
    </div>
    <div class="col-sm-9 user-view" style="display:none;">
    <div class="col-sm-6">
        <div class="panel panel-default">
            <div class="panel-heading"><h3 class="panel-title">User</h3></div>
            <div class="panel-body user-edit"></div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="panel panel-default">
            <div class="panel-heading"><h3 class="panel-title">Site Permissions</h3></div>
            <div class="panel-body user-site-permissions"></div>
        </div>
        <div class="panel panel-default">
            <div class="panel-heading"><h3 class="panel-title">Module Permissions</h3></div>
            <div class="panel-body user-module-permissions"></div>
        </div>
        <div class="panel panel-default">
            <div class="panel-heading"><h3 class="panel-title">Groups</h3></div>
            <div class="panel-body user-groups"></div>
        </div>
        <div class="panel panel-default">
            <div class="panel-heading"><h3 class="panel-title">Module Assignments</h3></div>
            <div class="panel-body user-module-assignments"></div>
        </div>
    </div>
    </div>
</div>
`);

user_groups_template = `
<ul>
    {{#pivot_groups}}
        <li><a href="/admin/groups/{{id}}/members">{{name}}</a> ({{pivot.type}})</li>
    {{/pivot_groups}}
</ul>
{{^pivot_groups}}
    <div class="alert alert-warning">No Group Memberships</div>
{{/pivot_groups}}
`;

user_module_permissions_template = `
<ul>
    {{#owned_modules}}
        <li><a href="/admin/modules">{{name}}</a> (owner)</li>
    {{/owned_modules}}
    {{#pivot_module_permissions}}
        <li><a href="/admin/modules/{{id}}/permissions">{{name}}</a> ({{pivot.permission}})</li>
    {{/pivot_module_permissions}}
</ul>
`;

user_module_assignments_template = `
<ul>
    {{#pivot_module_assignments}}
        <li><a href="/admin/modules/{{module_id}}/assignments">{{name}}</a> ({{pivot.status}})</li>
    {{/pivot_module_assignments}}
</ul>
{{^pivot_module_assignments}}
    <div class="alert alert-warning">No Module Assignments</div>
{{/pivot_module_assignments}}
<a href="/admin/users/{{id}}/assignments" class="btn btn-default">Manage Assignments</a>
`;

// Create New User
$('.user-new').on('click',function() {
    new gform(
        {"fields":user_form_attributes,
        "title":"Create New User",
        "actions":[
            {"type":"save"}
        ]}
    ).modal().on('save',function(form_event) {
        ajax.post('/api/users',form_event.form.get(),function(data) {
            form_event.form.trigger('close');
        });
    });
})

new gform(
    {"fields":[
        {
            "type": "user",
            "label": "Search Existing Users",
            "name": "user",
        }    
    ],
    "el":".user-search",
    "actions":[
        {"type":"save","label":"Submit","modifiers":"btn btn-primary"}
    ]
}
).on('change',function(form_event) {
    form_data = form_event.form.get();
    if (form_data.user == null || form_data.user == '') {
        $('.user-view').hide();
    }
}).on('save',function(form_event) {
    form_data = form_event.form.get();
    if (form_data.user != null && form_data.user != '') {
        user_id = form_data.user;
        ajax.get('/api/users/'+form_data.user,function(data) {
            $('.user-view').show();
            // Show Groups
            $('.user-groups').html(gform.m(user_groups_template,data));
            // Show Module Permissions
            $('.user-module-permissions').html(gform.m(user_module_permissions_template,data));
            // Show Module Assignments
            $('.user-module-assignments').html(gform.m(user_module_assignments_template,data));
            // Edit User
            new gform(
                {"fields":user_form_attributes,
                "el":".user-edit",
                "data":data,
                "actions":[
                    {"type":"save","label":"Update User","modifiers":"btn btn-primary"},
                    {"type":"button","label":"Delete User","action":"delete","modifiers":"btn btn-danger"}
                ]}
            ).on('delete',function(form_event) {
                form_data = form_event.form.get();
                if (confirm('Are you super sure you want to do this?  This action cannot be undone!')){
                    ajax.delete('/api/users/'+form_data.id,{},function(data) {
                        $('.user-view').hide();
                    });
                }
            }).on('save',function(form_event) {
                form_data = form_event.form.get();
                ajax.put('/api/users/'+form_data.id,form_data,function(data) {});
            });
            // end
            // Edit Permissions
            new gform(
                {"fields":[
                    {
                        "type": "radio",
                        "label": "Permissions",
                        "name": "permissions",
                        "multiple": true,
                        "options": [
                            {
                                "label": "Manage Users",
                                "value": "manage_users"
                            },
                            {
                                "label": "Manage User Permissions",
                                "value": "manage_user_permissions"
                            },
                            {
                                "label": "Manage Reports",
                                "value": "manage_reports"
                            },
                            {
                                "label": "Run Reports",
                                "value": "run_reports"
                            },
                            {
                                "label": "Manage Groups",
                                "value": "manage_groups"
                            },
                            {
                                "label": "Manage Modules",
                                "value": "manage_modules"
                            },
                            {
                                "label": "Assign Modules",
                                "value": "assign_modules"
                            },
                            {
                                "label": "Manage Bulk Assignments",
                                "value": "manage_bulk_assignments"
                            }
                        ]
                    }    
                ],
                "el":".user-site-permissions",
                "data":{"permissions":data.user_permissions},
                "actions":[
                    {"type":"save","label":"Update Permissions","modifiers":"btn btn-primary"}
                ]}
            ).on('save',function(form_event) {
                ajax.put('/api/users/'+user_id+'/permissions',form_event.form.get(),function(data) {});
            });
            // end

        });
    } else {
        $('.user-view').hide();
    }
});
