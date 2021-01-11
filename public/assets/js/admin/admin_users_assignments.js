ajax.get('/api/users/'+id+'/assignments',function(data) {
    gdg = new GrapheneDataGrid({el:'#adminDataGrid',
    item_template: gform.stencils['table_row'],
    search: false,columns: false,upload:false,download:false,title:'Users',
    entries:[],
    actions:[
        {"name":"create","label":"Add Module Assignment"},
        '',
        // {"name":"edit"},
        {"label":"View Report","name":"report","min":1,"max":1,"type":"default"},
        {"name":"complete","label":"Mark as Completed","min":1,"max":1,"type":"danger"},
        '',
        {"name":"delete","label":"Remove Module Assignment"}

    ],
    count:20,
    schema:[
        {type:"hidden", name:"id"},
        {type:"select", name:"module_id", label:"Module",options:"/api/modules",format:{label:"{{name}}", value:"{{id}}"}},
        {type:"text",name:"version", label:"Module Version", parse:false,show:false,template:"{{attributes.version.name}}"},
        {type:"datetime", name:"date_assigned", show:false, parse: false, label:"Date Assigned",format: {
            input: "YYYY-MM-DD HH:mm:ss"
        }},
        {type:"datetime", name:"date_due", label:"Date Due",format: {
            input: "YYYY-MM-DD HH:mm:ss"
        },required:true},
        {type:"text", name:"date_started", label:"Date Started", show:false, parse:false},
        {type:"text", name:"date_completed", label:"Date Completed", show:false, parse:false},
    ], data: data
    // Can't Update an assignment
    // }).on("model:edited",function(grid_event) {
    //     ajax.put('/api/users/'+id+'/assignments/'+grid_event.model.attributes.id,grid_event.model.attributes,function(data) {
    //         grid_event.model.attributes = data;
    //     });
    }).on("model:deleted",function(grid_event) {
        ajax.delete('/api/users/'+id+'/assignments/'+grid_event.model.attributes.id,{},function(data) {},function(data) {
            grid_event.model.undo();
        });
    }).on("model:created",function(grid_event) {
        ajax.post('/api/users/'+id+'/assignments/'+grid_event.model.attributes.module_id,grid_event.model.attributes,function(data) {
            grid_event.model.update(data);
            // grid_event.model.attributes = data;
            // grid_event.model.draw();
        },function(data) {
            // grid_event.model.update(data);
            grid_event.model.undo();
        });
    }).on("model:report",function(grid_event) {
        template = `
        <table class="table">
        <tbody>
        <tr><td>User</td><td>{{user.first_name}} {{user.last_name}}</td></tr>
        <tr><td>Module</td><td>{{version.name}}</td></tr>
        <tr><td>Status</td><td>{{status}}</td></tr>
        <tr><td>Score</td><td>{{score}}</td></tr>
        <tr><td>Duration</td><td>{{duration}}</td></tr>
        <tr><td>Date Assigned</td><td>{{date_assigned}}</td></tr>
        <tr><td>Date Due</td><td>{{date_due}}</td></tr>
        <tr><td>Date Started</td><td>{{date_started}}</td></tr>
        <tr><td>Date Completed</td><td>{{date_completed}}</td></tr>
        </tbody>
        </table>
        `;
        $('#adminModal .modal-title').html('Module Report')
        $('#adminModal .modal-body').html(gform.m(template,grid_event.model.attributes));
        $('#adminModal').modal('show')
    }).on('model:complete',function(grid_event) {
        data = grid_event.model.attributes.assignment || {};
        new gform(
            {
                "fields": [
                    {
                        "type": "radio",
                        "label": "Status",
                        "name": "status",
                        "required":"show",
                        "options": [
                            {
                                "label": "Attended",
                                "value": "attended"
                            },
                            {
                                "label": "Completed",
                                "value": "completed"
                            },
                            {
                                "label": "Passed",
                                "value": "passed"
                            },
                            {
                                "label": "Incomplete",
                                "value": "incomplete"
                            }
                        ]
                    },
                    {
                        "type":"checkbox",
                        "name":"specify_start_date",
                        "label":"Specify Start Date",
                        "columns":6
                    },
                    {
                        "type":"datetime",
                        "label":"Date and Time Started",
                        "name":"date_started",
                        "format": {
                            "input": "YYYY-MM-DD HH:mm:ss"
                        },
                        "show": [
                            {
                                "name": "specify_start_date",
                                "type": "matches",
                                "value": [
                                    true
                                ]
                            }
                        ],
                        "required":"show",
                        "columns":6
                    },
                    {
                        "type":"checkbox",
                        "name":"specify_completed_date",
                        "label":"Specify Date Completed",
                        "columns":6,
                        "forceRow": true,
                    },
                    {
                        "type":"datetime",
                        "label":"Date and Time Completed",
                        "name":"date_completed",
                        "format": {
                            "input": "YYYY-MM-DD HH:mm:ss"
                        },
                        "show": [
                            {
                                "name": "specify_completed_date",
                                "type": "matches",
                                "value": [
                                    true
                                ]
                            }
                        ],
                        "required":"show",
                        "columns":6
                    },
                    {
                        "type":"text",
                        "label":"Score",
                        "name":"score",
                        "value":100,
                    }
                ],
                "data": data
            })
            .modal().on('save', function (form_event) {
            // console.log(form_event.form.get());
            if(form_event.form.validate()){
                ajax.put('/api/assignment/' + grid_event.model.attributes.id + '/complete', form_event.form.get(), function (data) {
                    // grid_event.model.attributes.assignment = data;
                    // grid_event.model.draw();
                    grid_event.model.update(data)
                    form_event.form.trigger('close');
                }, function (err) {
                    grid_event.model.undo();
                    // console.log(data.response)
                })
            }
        }).on('cancel', function (form_event) {
            form_event.form.trigger('close');
        })
    })
});

// Built-In Events:
//'edit','model:edit','model:edited','model:create','model:created','model:delete','model:deleted'


