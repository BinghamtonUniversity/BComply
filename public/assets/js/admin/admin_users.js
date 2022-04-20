gform.options = {autoFocus:false};
user_form_attributes = [
    {type:"hidden", name:"id"},
    {type:"checkbox", name:"active", label:"Active", value:true},
    {type:"text", name:"unique_id", label:"Unique ID", required:true},
    {type:"text", name:"first_name", label:"First Name"},
    {type:"text", name:"last_name", label:"Last Name"},
    {type:"email", name:"email", label:"Email", required:true},
    {type:"text", name:"payroll_code", label:"Payroll Code"},
    {type:"text", name:"supervisor", label:"Supervisor"},
    {type:"text", name:"department_id", label:"Department ID"},
    {type:"text", name:"department_name", label:"Department Name"},
    {type:"text", name:"division_id", label:"Division ID"},
    {type:"text", name:"division", label:"Division Name"},
    {type:"text", name:"negotiation_unit", label:"Negotiation Unit"},
    {type:"text", name:"title", label:"Title"},
    {type:"text", name:"role_type", label:"Role Type"},
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
                <div class="btn btn-success user-new">Create New User</div><br><br>
                <div class="btn btn-warning bulk-inactivate-users">Bulk Inactivate Users</div>
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

// Bulk Inactivate Users
$('.bulk-inactivate-users').on('click',function() {
    new gform({
        "legend":"Bulk Inactivate Users",
        "name": "bulk_inactivate_users",
        "fields": [
            {
                "type": "textarea",
                "label": "Unique IDs",
                "name": "unique_ids",
                "showColumn": true,
                "help":"Please enter a list of Unique IDs (BNumbers). " +
                    "You can either use a \",\" (comma) to separate them or enter them in separate lines"
            }
        ]
    }).on('save',function(form_event){
        toastr.info('Processing... Please Wait')
        form_event.form.trigger('close');
        ajax.post('/api/users/bulk_inactivate',form_event.form.get(),function(data) {
            // do something
            template = `
                {{#count}}
                    <div class="alert alert-success">
                        <h5>The Following users were inactivated:</h5>
                        <ul>
                        {{#users}}
                            <li>{{first_name}} {{last_name}}</li>
                        {{/users}}
                        </ul>
                    </div>
                {{/count}}
                {{^count}}
                    <div class="alert alert-danger">
                        No Users were Inactivated.
                    </div>
                {{/count}}
                `;
            $('#adminModal .modal-title').html('Inactivation Status')
            $('#adminModal .modal-body').html(gform.m(template,data));
            $('#adminModal').modal('show')
        },function(data){
            // do nothing
        });
    }).on('cancel',function(form_event){
        form_event.form.trigger('close');
    }).modal()
});

// Create New User
$('.user-new').on('click',function() {
    new gform(
        {"fields":user_form_attributes,
        "title":"Create New User",
        "actions":[
            {"type":"save"}
        ]}
    ).modal().on('save',function(form_event) {
        if(form_event.form.validate())
        {
            ajax.post('/api/users', form_event.form.get(), function (data) {
                form_event.form.trigger('close');
            });
        }
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
                    {"type":"button","label":"Delete User","action":"delete","modifiers":"btn btn-danger"},
                    {"type":"button","label":"Merge Into","action":"merge_user","modifiers":"btn btn-danger"},
                    {"type":"button","label":"Login","action":"login","modifiers":"btn btn-warning"}
                ]}
            ).on('delete',function(form_event) {
                form_data = form_event.form.get();
                if (confirm('Are you super sure you want to do this?  This action cannot be undone!')){
                    ajax.delete('/api/users/'+form_data.id,{},function(data) {
                        $('.user-view').hide();
                    });
                }
            }).on('merge_user',function(form_event) {
                form_data = form_event.form.get();
                source_user = form_data.id;
                new gform(
                    {"fields":[{
                        "type": "user",
                        "label": "Target User",
                        "name": "target_user",
                        "required":true,          
                    },{type:"checkbox", name:"delete", label:"Delete Source User", value:false,help:"By checking this box, the `source` user will be irretrievably deleted from BComply."},
                    {type:"output",parse:false,value:'<div class="alert alert-danger">This action will migrate/transfer all assignments from the source user to the specified target user.  This is a permanent and "undoable" action.</div>'}],
                    "title":"Merge Into",
                    "actions":[
                        {"type":"cancel"},
                        {"type":"button","label":"Commit Merge","action":"save","modifiers":"btn btn-danger"},
                    ]}
                ).modal().on('save',function(merge_form_event) {
                    var merge_form_data = merge_form_event.form.get();
                    if(form_event.form.validate() && merge_form_data.target_user !== '')
                    {
                        if (confirm("Are you sure you want to merge these users?  This action cannot be undone!")) {
                            ajax.put('/api/users/'+source_user+'/merge_into/'+merge_form_data.target_user, {delete:merge_form_data.delete}, function (data) {
                                merge_form_event.form.trigger('close');
                                if (_.has(data,'errors')) {
                                    toastr.error('One or more errors occurred.')
                                    console.log(data.errors);
                                    window.alert(data.errors.join("\n"))
                                } else {
                                    toastr.success('User Merge Successful!');
                                }
                            });
                        }
                    }
                }).on('cancel',function(merge_form_event) {
                    merge_form_event.form.trigger('close');
                });            
            }).on('save',function(form_event) {
                if(form_event.form.validate())
                {
                    form_data = form_event.form.get();
                    ajax.put('/api/users/' + form_data.id, form_data, function (data) {
                    });
                }
            }).on('login',function(form_event) {
                form_data = form_event.form.get();
                ajax.post('/api/login/'+form_data.id,{},function(data) {
                    window.location = '/';
                });
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
                                "label": "Impersonate Users",
                                "value": "impersonate_users"
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
                            },
                            {
                                "label": "Manage Workshops",
                                "value": "manage_workshops"
                            },
                            {
                                "label": "Assign Workshops",
                                "value": "assign_workshops"
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
