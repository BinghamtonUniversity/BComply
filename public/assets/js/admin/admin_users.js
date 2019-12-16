gform.options = {autoFocus:false};
user_form_attributes = [
    {type:"hidden", name:"id"},
    {type:"checkbox", name:"active", label:"Active", value:true},
    {type:"text", name:"first_name", label:"First Name"},
    {type:"text", name:"last_name", label:"Last Name"},
    {type:"text", name:"unique_id", label:"Unique ID"},
    {type:"email", name:"email", label:"Email"},
    {type:"text", name:"code", label:"Code"},
    {type:"text", name:"supervisor", label:"Supervisor"},
    {type:"text", name:"department", label:"Department"},
    {type:"text", name:"division", label:"Division"},
    {type:"text", name:"title", label:"Title"},
];

$('#adminDataGrid').html(`
<div class="row">
    <div class="col-sm-12 actions">
        <div class="btn btn-success user-new">Create New User</div>
    </div>
</div><hr>
<div class="row">
    <div class="col-sm-4 user-search">
    </div>
    <div class="col-sm-8 user-view" style="display:none;">
        <div class="panel panel-default">
            <div class="panel-heading"><h3 class="panel-title">User</h3></div>
            <div class="panel-body">
                <div class="btn btn-default user-assignments">Manage Assignments</div>
                <div class="btn btn-default user-groups">Manage Groups</div>
                <div class="btn btn-danger user-delete">Delete User</div>
            </div>
            <div class="panel-body user-edit"></div>
        </div>
        <div class="panel panel-default">
            <div class="panel-heading"><h3 class="panel-title">Permissions</h3></div>
            <div class="panel-body user-permissions"></div>
        </div>
    </div>
</div>
`);

// Create New User
$('.user-new').on('click',function() {
    new gform(
        {"fields":user_form_attributes,
        "title":"Create New User",
        "actions":[
            {"type":"save"}
        ]}
    ).modal().on('save',function(form_event) {
        ajax.post('/api/users/',form_event.form.get(),function(data) {
            form_event.form.trigger('close');
        });
    });
})

new gform(
    {"fields":[
        {
            "type": "user",
            "label": "User Search",
            "name": "user",
        }    
    ],
    "el":".user-search",
    "actions":[{"type":"save","label":"Submit","modifiers":"btn btn-primary"}]
}
).on('save',function(form_event) {
    form_data = form_event.form.get();
    if (form_data.user != null && form_data.user != '') {
        user_id = form_data.user;
        ajax.get('/api/users/'+form_data.user,function(data) {
            $('.user-view').show();
            // Manage Assignments
            $('.user-assignments').on('click',function() {
                window.location = '/admin/users/'+user_id+'/assignments';
            });
            // Manage Groups
            $('.user-groups').on('click',function() {
                window.location = '/admin/users/'+user_id+'/groups';
            });
            // Delete User
            $('.user-delete').on('click',function() {
                if (confirm('Are you super sure you want to do this?  This action cannot be undone!')){
                    ajax.delete('/api/users/'+user_id,{},function(data) {
                        $('.user-view').hide();
                    });
                }
            });
            // Edit User
            new gform(
                {"fields":user_form_attributes,
                "el":".user-edit",
                "data":data,
                "actions":[
                    {"type":"save","label":"Update User","modifiers":"btn btn-primary"}
                ]}
            ).on('save',function(form_event) {
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
                "el":".user-permissions",
                "data":{"permissions":data.user_permissions},
                "actions":[
                    {"type":"save","label":"Update Permissions","modifiers":"btn btn-primary"}
                ]}
            ).on('save',function(form_event) {
                ajax.put('/api/users/'+user_id+'/permissions',form_event.form.get(),function(data) {});
            });
            // end

        });
    }
});
