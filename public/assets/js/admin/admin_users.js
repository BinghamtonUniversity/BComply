ajax.get('/api/users',function(data) {
    gdg = new GrapheneDataGrid({el:'#adminDataGrid',
    search: false,columns: false,upload:false,download:false,title:'Users',
    entries:[],
    actions:actions,
    count:20,
    schema:[
        {type:"hidden", name:"id"},
        {type:"checkbox", name:"active", label:"Active", "template":"{{#attributes.active}}Yes{{/attributes.active}}{{^attributes.active}}No{{/attributes.active}}"},
        {type:"text", name:"first_name", label:"First Name"},
        {type:"text", name:"last_name", label:"Last Name"},
        {type:"text", name:"unique_id", label:"Unique ID"},
        {type:"email", name:"email", label:"Email"},
        {type:"text", name:"code", label:"Code"},
        {type:"text", name:"supervisor", label:"Supervisor"},
        {type:"text", name:"department", label:"Department"},
        {type:"text", name:"division", label:"Division"},
        {type:"text", name:"title", label:"Title"},
    ], data: data
    }).on("model:edited",function(grid_event) {
        ajax.put('/api/users/'+grid_event.model.attributes.id,grid_event.model.attributes,function(data) {
            grid_event.model.attributes = data;
            grid_event.model.draw();
        },function(err) {
            // toastr.error(err);
            grid_event.model.undo();
        });
    }).on("model:created",function(grid_event) {
        ajax.post('/api/users',grid_event.model.attributes,function(data) {
            grid_event.model.attributes = data;
            grid_event.model.draw();
        },function(err) {
            // toastr.error(err);
            grid_event.model.undo();

        });
    }).on("model:deleted",function(grid_event) {
        ajax.delete('/api/users/'+grid_event.model.attributes.id,{},function(data) {
            grid_event.model.draw();
        },function(data) {
            grid_event.model.undo();
        },function(data) {
            grid_event.model.undo();
        });
    }).on("model:edit_perm",function(grid_event) {
        user_id = grid_event.model.attributes.id;
        user_permissions = grid_event.model.attributes.user_permissions;
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
            "data":{"permissions":user_permissions},
            "actions":[
                {"type":"save"}
            ]}
        ).modal().on('save',function(form_event) {
            ajax.put('/api/users/'+user_id+'/permissions',form_event.form.get(),function(data) {
                form_event.form.trigger('close');
                // Commenting out for now, as not helpful for the majority of use cases
                // (Changing other people's permissions vs. changing own permissions)
                // window.setTimeout(function(){window.location.reload()}, 1000);
            });
        });
    }).on("model:assignments",function(grid_event) {
        window.location = '/admin/users/'+grid_event.model.attributes.id+'/assignments';
    })
});

// Built-In Events:
//'edit','model:edit','model:edited','model:create','model:created','model:delete','model:deleted'


