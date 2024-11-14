ajax.get('/api/modules/'+id+'/assignments',function(data) {
    gdg = new GrapheneDataGrid({el:'#adminDataGrid',
    item_template: gform.stencils['table_row'],
    search: false,columns: false,upload:false,download:false,title:'Users',
    entries:[],
    actions:[
        {"name":"add_assignment","label":"Add Module Assignment",type:"success"},
        '',
        {"label":"Mark As Completed","name":"complete","min":1,"max":10000,"type":"danger"},
        {"label":"View Report","name":"report","min":1,"max":1,"type":"default"},
        '',
        {"name":"delete","label":"Remove Module Assignment"}
    ],
    count:20,
    schema:[
        {type:"hidden", name:"id"},
        {type:"text",name:"version", label:"Module Version"},
        {type:"text", name:"first", label:"First Name"},
        {type:"text", name:"last", label:"Last Name"},
        {type:"select", name:"status", label:"Status", options:[
            {"label": "Assigned","value": "assigned"},
            {"label": "Attended","value": "attended"},
            {"label": "Completed","value": "completed"},
            {"label": "Passed","value": "passed"},
            {"label": "Incomplete","value": "incomplete"}
        ]},
        {type:"text", name:"assigned", label:"Assigned"},
        {type:"text", name:"due", label:"Due"},
        {type:"text", name:"started", label:"Started"},
        {type:"text", name:"completed", label:"Completed"}
    ], data: data
    }).on("model:deleted",function(grid_event) {
        ajax.delete('/api/users/'+grid_event.model.attributes.user_id+'/assignments/'+grid_event.model.attributes.id,{},function(data) {},function(data) {
            grid_event.model.undo();
        });
    }).on('add_assignment',function(grid_event) {
        new gform({
            "fields": [
                {type:"text",name:"version", label:"Module Version", parse:false,show:false,template:"{{attributes.version.name}}"},
                {type:"user", name:"user_id",required:true, label:"User", template:"{{attributes.user.first_name}} {{attributes.user.last_name}}"},
                {type:"datetime", name:"date_assigned", label:"Date Assigned",parse:false,show:false,format: {
                    input: "YYYY-MM-DD HH:mm:ss"
                }},
                {type:"datetime", name:"date_due", label:"Date Due",
                    format: {input: "YYYY-MM-DD HH:mm:ss" },
                    required: true
                },
                {type:"text", parse:false,show:false, name:"date_started", label:"Date Started"},
                {type:"text", parse:false,show:false, name:"date_completed", label:"Date Completed"},        
            ]
        }).modal().on('save', function (form_event) {
            var form_data = form_event.form.get();
            if(form_event.form.validate()){
                ajax.post('/api/users/'+form_data.user_id+'/assignments/'+id,form_data,function(data) {
                    // Remap Object
                    var assignment = {
                        user_id: data.user.id,
                        id:data.id,
                        first:data.user.first_name,
                        last:data.user.last_name,
                        version:data.version.name,
                        status:data.status,
                        score:data.score,
                        duration:data.duration,
                        assigned:data.date_assigned,
                        due:data.date_due,
                        started:data.date_started,
                        completed:data.date_completed
                    };
                    grid_event.grid.add(assignment)
                    form_event.form.trigger('close');
                },function(data) {
                    // Do Nothing!
                });
            }
        }).on('cancel', function (form_event) {
            form_event.form.trigger('close');
        });
    }).on("model:report",function(grid_event) {
        template = `
        <table class="table">
        <tbody>
        <tr><td>User</td><td>{{first}} {{last}}</td></tr>
        <tr><td>Module</td><td>{{version}}</td></tr>
        <tr><td>Status</td><td>{{status}}</td></tr>
        <tr><td>Score</td><td>{{score}}</td></tr>
        <tr><td>Duration</td><td>{{duration}}</td></tr>
        <tr><td>Date Assigned</td><td>{{assigned}}</td></tr>
        <tr><td>Date Due</td><td>{{due}}</td></tr>
        <tr><td>Date Started</td><td>{{started}}</td></tr>
        <tr><td>Date Completed</td><td>{{completed}}</td></tr>
        </tbody>
        </table>
        `;
        $('#adminModal .modal-title').html('Module Report')
        $('#adminModal .modal-body').html(gform.m(template,grid_event.model.attributes));
        $('#adminModal').modal('show')
    }).on('complete',function(grid_event) {
        var incomplete_models = _.filter(grid_event.grid.getSelected(),function(current_model) {
            return current_model.attributes.completed === null;
        })
        if (grid_event.grid.getSelected().length !== incomplete_models.length) {
            toastr.error('One or more of the assignments you have selected have already been completed. You can only mark incomplete items as completed!');
            return;
        }
        if (grid_event.grid.getSelected().length > 1) {
            if (!confirm('You have selected ' + grid_event.grid.getSelected().length + ' assignments to bulk update. Are you sure you want to continue?')) {
                toastr.error('Exiting without updating.');
                return;
            }
        }
        // assignment_data = grid_event.model.attributes || {};
        var update_form = new gform(
            {
                "fields": [
                    {
                        "type": "radio",
                        "label": "Status",
                        "name": "status",
                        "required":true,
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
                ]
        }).on('save', function (form_event) {
            if(form_event.form.validate()) {
                var form_data = form_event.form.get();
                form_event.form.trigger('close');
                _.each(grid_event.grid.getSelected(), function(selected_model) {
                    toastr.info('Processing Assignment '+selected_model.attributes.id + '...')
                    ajax.put('/api/assignment/' + selected_model.attributes.id + '/complete', form_data, function (data) {
                        data.started = data.date_started;
                        data.completed = data.date_completed;
                        selected_model.update(data)
                    }, function (err) {
                        // Do Nothing
                    })
                })
            }
        }).on('cancel', function (form_event) {
            form_event.form.trigger('close');
        })
        update_form.modal().set({});
    })
});