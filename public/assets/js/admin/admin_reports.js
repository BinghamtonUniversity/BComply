ajax.get('/api/reports',function(data) {

    gdg = new GrapheneDataGrid({el:'#adminDataGrid',
    item_template: gform.stencils['table_row'],
    search: false,columns: false,upload:false,download:false,title:'Reports',
    entries:[],
    actions:actions,
    count:20,
    schema:[
        {type:"hidden", name:"id"},
        {type:"text", name:"name", label:"Report Name",required:true},
        {type:"textarea", name:"description", label:"Description"},
        {type:"user", name:"owner_user_id", label:"Owner",required:true, template:"{{attributes.owner.first_name}} {{attributes.owner.last_name}}"},
        {type:"output", label:"",name:"out_txt", format:{value:"<div class='alert alert-info'>Use the fields below to specify additional users who will have read only access to this report</div>"},showColumn:false},
        {type:"user", name:"permissions", label:"User",required:false, array: {min:0,max:50}, showColumn:false},

    ], data: data
    }).on("model:created",function(grid_event) {
        ajax.post('/api/reports',grid_event.model.attributes,function(data) {
            grid_event.model.attributes = data;
            grid_event.model.draw();
        },function(data) {
            grid_event.model.undo();
        });
    }).on("model:edited",function(grid_event) {
        ajax.put('/api/reports/'+grid_event.model.attributes.id,grid_event.model.attributes,function(data) {
            grid_event.model.update(data);
            // grid_event.model.draw();
        },function(data) {
            grid_event.model.undo();
        });
    }).on("model:deleted",function(grid_event) {
        ajax.delete('/api/reports/'+grid_event.model.attributes.id,{},function(data) {},function(data) {
            grid_event.model.undo();
        });
    }).on("model:run_report",function(grid_event) {
        window.location = '/admin/reports/'+grid_event.model.attributes.id+'/run';
    }).on("model:configure_query",function(grid_event) {
        report_id = grid_event.model.attributes.id;
        report = grid_event.model.attributes.report || {};
        new gform(
            {
                "legend" : "Query Builder",
                "fields": [
                    {
                        "type": "radio",
                        "label": "Additional Columns",
                        "name": "columns",
                        "multiple": true,
                        "options": [
                            {"label": "user_id", "value": "users.unique_id as user_id"},
                            {"label": "payroll_code", "value": "payroll_code"},
                            {"label": "supervisor", "value": "supervisor"},
                            {"label": "department_id", "value": "department_id"},
                            {"label": "department_name", "value": "department_name"},
                            {"label": "division_id", "value": "division_id"},
                            {"label": "division", "value": "division"},
                            {"label": "negotiation_unit", "value": "negotiation_unit"},
                            {"label": "title", "value": "title"},
                            {"label": "role_type", "value": "role_type"},
                            {"label": "active", "value": "active"},
                            {"label": "group_memberships", "value": "user_groups.groups as group_memberships"},
                            {"label": "date_started", "value": "date_started"},
                            {"label": "date_due", "value": "date_due"},
                            {"label": "date_completed", "value": "date_completed"},
                            {"label": "status", "value": "module_assignments.status as status"},
                            {"label": "score", "value": "score"},
                            {"label": "duration", "value": "duration"},                
                        ]
                    },
                    {
                            "type": "select",
                            "label": "Global AND / OR",
                            "name": "and_or",
                            "value": "and",
                            "multiple": false,
                            "options": [
                                {
                                    "label": "",
                                    "type": "optgroup",
                                    "options": [
                                        {
                                            "label": "AND",
                                            "value": "and"
                                        },
                                        {
                                            "label": "OR",
                                            "value": "or"
                                        }
                                    ]
                                }
                            ]
                        },
                        {
                        "label": false,
                        "name": "block",
                        "array": {
                            "min": 1,
                            "max": null
                        },
                        "fields": [
                            {
                                "type": "select",
                                "label": "AND / OR",
                                "name": "and_or",
                                "value": "and",
                                "multiple": false,
                                "options": [
                                    {
                                        "label": "",
                                        "type": "optgroup",
                                        "options": [
                                            {
                                                "label": "AND",
                                                "value": "and"
                                            },
                                            {
                                                "label": "OR",
                                                "value": "or"
                                            }
                                        ]
                                    }
                                ]
                            },
                    {
                        "label": false,
                        "name": "check",
                        "array": {
                            "min": 1,
                            "max": null
                        },
                        "fields": [
                            {
                                "type": "smallcombo",
                                "label": "Column",
                                "name": "column",
                                "columns": "4",
                                "options":"/api/reports/tables/columns",
                                "required":"show"
                            },
                            {
                                "type": "select",
                                "label": "Conditional",
                                "name": "conditional",
                                "display": "",
                                "multiple": false,
                                "columns": "4",
                                "forceRow": false,
                                "options": [
                                    {
                                        "label": "",
                                        "type": "optgroup",
                                        "options": ['=','!=','>','>=','<','<=','is_null','not_null','contains']
                                    }
                                ],
                                "widgetType": "collection",
                                "editable": true
                            },
                            {
                                "type": "text",
                                "label": "Value",
                                "name": "value",
                                "columns": "4",
                                show: [{type: "matches",name: "conditional",value: ['=','!=','>','>=','<','<=','contains']}],
                                "required":"show"
                            }
                        ],
                        "type": "fieldset"
                    }

                        ],
                        "type": "fieldset"
                    }
                ],
                "data": report
            }
        ).modal().on('save',function(form_event) {
            if(form_event.form.validate()){
                ajax.put('/api/reports/' + report_id, {'report': form_event.form.get()}, function (data) {
                    // grid_event.model.attributes.report = data
                    grid_event.model.update(data);
                    form_event.form.trigger('close');
                });
            }
        }).on('cancel',function(form_event) {
            form_event.form.trigger('close');
        });
    })
});

// Built-In Events:
//'edit','model:edit','model:edited','model:create','model:created','model:delete','model:deleted'


