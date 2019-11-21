ajax.get('/api/users',function(data) {
    gdg = new GrapheneDataGrid({el:'#adminDataGrid',
    search: false,columns: false,upload:false,download:false,title:'Users',
    entries:[],
    actions:[
        {"name":"create","label":"Add User"},
        '',
        {"name":"edit","label":"Edit User"},
        {"label":"Edit Permissions","name":"edit_perm","min":1,"max":1,"type":"default"},
        {"label":"Manage Assignments","name":"assignments","min":1,"max":1,"type":"default"},
        '',
        {"name":"delete","label":"Delete User"}
    ],
    count:4,
    schema:[
        {type:"hidden", name:"id"},
        {type:"text", name:"first_name", label:"First Name"},
        {type:"text", name:"last_name", label:"Last Name"},
        {type:"text", name:"unique_id", label:"Unique ID"},
        {type:"email", name:"email", label:"Email"},
        // {
        //     label: "Additional Parameters",
        //     name: "params",
        //     array: false,
        //     fields: [
        //         {type:"text", name:"payroll_code", label:"Payroll Code"},
        //         {type:"text", name:"supervisor", label:"Supervisor"},
        //         {type:"text", name:"org", label:"Org (Department)"},
        //         {type:"text", name:"l3org", label:"L3 Org"},
        //         {type:"text", name:"job_title", label:"Job Title"},
        //         {type:"text", name:"preferred_name", label:"Preferred Name"},
        //         {type:"checkbox", name:"active", label:"Active"},
        //     ],
        //     type: "fieldset"
        // }
    ], data: data
    }).on("model:edited",function(grid_event) {
        ajax.put('/api/users/'+grid_event.model.attributes.id,grid_event.model.attributes,function(data) {
            grid_event.model.attributes = data;
        });
    }).on("model:created",function(grid_event) {
        ajax.post('/api/users',grid_event.model.attributes,function(data) {
            grid_event.model.attributes = data;
        });
    }).on("model:deleted",function(grid_event) {
        ajax.delete('/api/users/'+grid_event.model.attributes.id,{},function(data) {});
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
                            "label": "Report",
                            "value": "report"
                        },
                        {
                            "label": "Manage Groups",
                            "value": "manage_groups"
                        },
                        {
                            "label": "Manage Modules",
                            "value": "manage_modules"
                        },
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
            });
        });
    }).on("model:assignments",function(grid_event) {
        window.location = '/admin/users/'+grid_event.model.attributes.id+'/assignments';
    })
});

// Built-In Events:
//'edit','model:edit','model:edited','model:create','model:created','model:delete','model:deleted'


