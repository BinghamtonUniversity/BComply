ajax.get('/api/modules',function(data) {
    // debugger;
    create_fields = [
        {type:"hidden", name:"id"},
        {type:"checkbox", name:"public", label:"Public?","columns":6},
        {type:"checkbox", name:"past_due", label:"Allow After Due",columns:6},
        {type:"text", name:"name", label:"Name", columns:8, "required":true},
        {type:"text", name:"icon", label:"Icon", columns:4},
        {type:"textarea", name:"description", label:"Description Name"},
        {type:"user", name:"owner_user_id", label:"Owner", template:"{{attributes.owner.first_name}} {{attributes.owner.last_name}}", "required":true},
        {
            "type":"radio",
            "name":"reminders",
            "label":"Reminders Before Due Date",
            "multiple":true,
            "columns":6,
            "options":[
                {
                    "label":"< 24 Hours",
                    "value":0
                },
                {
                    "label":"1 Day",
                    "value":1
                },
                {
                    "label":"7 Days",
                    "value":7
                },
                {
                    "label":"14 Days",
                    "value":14
                },
                {
                    "label":"30 Days",
                    "value":30
                },
                {
                    "label":"60 Days",
                    "value":60
                }],
        },
        {
            "type":"radio",
            "name":"past_due_reminders",
            "label":"Reminders After Due Date",
            "multiple":true,
            "columns":6,
            "options":[
                {
                    "label":"30 Day",
                    "value":30
                },
                {
                    "label":"60 Days",
                    "value":60
                },
                {
                    "label":"90 Days",
                    "value":90
                },
                {
                    "label":"120 Days",
                    "value":120
                },
                {
                    "label":"150 Days",
                    "value":150
                }],
            "show": [
                {
                    "name": "past_due",
                    "type": "matches",
                    "value": [
                        true
                    ]
                }
            ],
        },
        {
            "name": "templates",
            "type": "fieldset",
            "editable":true,
            "label": "Email Templates",
            "fields": [
                {
                    "type":"output",
                    "name":"info_text",
                    "label":false,
                    "value":"<div class='alert alert-info'>Note: You may optionally leave templates blank to prevent triggering automated emails</div>",
                    "parse":false,
                },
                {
                    "type":"textarea",
                    "name":"assignment",
                    "id":"assignment",
                    "label":"Assignment Notification Template",
                    "template": "{{attributes.templates.assignment}}",
                    "value":
`{{user.first_name}} {{user.last_name}}<br>
<br>
This email serves as notification that you have been assigned the "{{module.name}}" training module, 
which is required to be completed by {{module.due_date}}.<br>
<br>
To complete this training, please utilize the following link: <a href="{{link}}">{{module.name}}</a>.`
                },
                {
                    "type":"textarea",
                    "name":"reminder",
                    "id":"reminder",
                    "label":"Assignment Reminder Template",
                    "template": "{{attributes.templates.reminder}}",
                    "value":
`{{user.first_name}} {{user.last_name}}<br>
<br>
This is a reminder that the "{{module.name}}" training module which was assigned to you
on {{module.assignment_date}}, is due on {{module.due_date}}.<br>
<br>
To complete this training, please utilize the following link: <a href="{{link}}">{{module.name}}</a>.`
                },
                {
                    "type":"textarea",
                    "name":"past_due_reminder",
                    "id":"past_due_reminder",
                    "label":"Assignment Past Due Reminder Template",
                    "template": "{{attributes.templates.past_due_reminder}}",
                    "show": [
                            {
                                "name": "past_due",
                                "type": "matches",
                                "value": [
                                    true
                                ]
                            }
                        ],
                    "value":
`{{user.first_name}} {{user.last_name}}<br>
<br>
This is a reminder that the "{{module.name}}" training module which was assigned to you
on {{module.assignment_date}}, is <b>past due</b> as of {{module.due_date}}.<br>
<br>
To complete this training, please utilize the following link: <a href="{{link}}">{{module.name}}</a>.`
                },
                {
                    "type":"textarea",
                    "name":"completion_notification",
                    "id":"completion_notification",
                    "label":"Assignment Complation Template",
                    "template": "{{attributes.templates.completion_notification}}",
                    "value":
`{{user.first_name}} {{user.last_name}}<br>
<br>
This email serves as confirmation that you have completed the "{{module.name}}" training module.<br>
<br>
You may view the confirmation certificate here: <a href="{{link}}">Certificate</a>`
                },
                {
                    "type":"textarea",
                    "name":"certificate",
                    "id":"certificate",
                    "label":"Completion Certificate Template",
                    "template": "{{attributes.templates.certificate}}",
                    "value":
`<h3>{{user.first_name}} {{user.last_name}}</h3> has completed<br>
<b>{{module.name}}</b> module <b>{{module.version_name}}</b><br>
at<br>
<b>{{assignment.data_completed}}</b>`
                }
            ]
        },
        {type:"text", show:false, parse:false, name:"current", label:"Current Version", template:"{{attributes.current_version.name}}"},
    ];
    gdg = new GrapheneDataGrid({el:'#adminDataGrid',
        search: false,columns: false,upload:false,download:false,title:'Users',
        entries:[],
        actions:actions,
        count:20,
        create:{fields:create_fields},
        edit:{fields:create_fields},
        schema:[
            {type:"hidden", name:"id"},
            {type:"checkbox", name:"public", label:"Public?", template:"{{#attributes.public}}Public{{/attributes.public}}{{^attributes.public}}Private{{/attributes.public}}"},
            {type:"text", name:"name", label:"Name"},
            {type:"textarea", name:"description", label:"Description Name"},
            {type:"user", name:"owner_user_id", label:"Owner", template:"{{attributes.owner.first_name}} {{attributes.owner.last_name}}"},
            {
                "type":"text",
                "name":"reminders",
                "label":"Reminders (Days)",
            },
            {
                "type":"text",
                "name":"past_due_reminders",
                "label":"Post Due Reminders (Days)",
            },
            {type:"text", show:false, parse:false, name:"current", label:"Current Version", template:"{{attributes.current_version.name}}"},
            {type:"checkbox", name:"past_due", label:"Past Due?",template:"{{#attributes.past_due}}Yes{{/attributes.past_due}}{{^attributes.past_due}}No{{/attributes.past_due}}"},
        ],data: data
    }).on("model:edited",function(grid_event) {
        ajax.put('/api/modules/'+grid_event.model.attributes.id,grid_event.model.attributes,function(data) {
            grid_event.model.update(data)
            // grid_event.model.update(data);
            // grid_event.model.draw();
        },function(data) {
            grid_event.model.undo();
        });
    }).on("model:created",function(grid_event) {
        ajax.post('/api/modules',grid_event.model.attributes,function(data) {
            grid_event.model.update(data)
            // grid_event.model.attributes = data;
            // grid_event.model.draw();
        },function(data) {
            grid_event.model.undo();
            grid_event.model.draw();
        });
    }).on("model:deleted",function(grid_event) {
        ajax.delete('/api/modules/'+grid_event.model.attributes.id,{},function(data) {},function(data) {
            grid_event.model.undo();
            grid_event.model.draw();
        });
    }).on("model:manage_versions",function(grid_event) {
        window.location = '/admin/modules/'+grid_event.model.attributes.id+'/versions';
    }).on("model:manage_admins",function(grid_event) {
        window.location = '/admin/modules/'+grid_event.model.attributes.id+'/permissions';
    }).on("model:manage_assignments",function(grid_event) {
        window.location = '/admin/modules/'+grid_event.model.attributes.id+'/assignments/';
    });
});
