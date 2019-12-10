ajax.get('/api/bulk_assignments',function(data) {
    gdg = new GrapheneDataGrid({el:'#adminDataGrid',
        search: false,columns: false,upload:false,download:false,title:'Bulk Assignments',
        entries:[],
        actions:[
            {"name":"create","label":"Create New Bulk Assignment"},
            '',
            {"name":"edit","label":"Edit Description"},
            {"label":"Configure","name":"configure_query","min":1,"max":1,"type":"default"},
            {"label":"Run (Test Only)","name":"run_test","min":1,"max":1,"type":"warning"},
            {"label":"Run","name":"run","min":1,"max":1,"type":"danger"},
            '',
            {"name":"delete","label":"Delete Bulk Assignment"}
        ],
        count:20,
        schema:[
            {type:"hidden", name:"id"},
            {type:"text", name:"name", label:"Assignment Name"},
            {type:"textarea", name:"description", label:"Description"}
        ], data: data
    })
        .on("model:created",function(grid_event) {
        ajax.post('/api/bulk_assignments',grid_event.model.attributes,function(data) {
            grid_event.model.attributes = data;
            grid_event.model.draw();
        },function(data) {
            grid_event.model.undo();
        });
    }).on("model:edited",function(grid_event) {
        ajax.put('/api/bulk_assignments/'+grid_event.model.attributes.id,grid_event.model.attributes,function(data) {
            grid_event.model.attributes = data;
            grid_event.model.draw();
        },function(data) {
            grid_event.model.undo();
        });
    }).on("model:deleted",function(grid_event) {
        ajax.delete('/api/bulk_assignments/'+grid_event.model.attributes.id,{},function(data) {},function(data) {
            grid_event.model.undo();
        });
    }).on("model:run_test",function(grid_event) {
        toastr.info('Processing... Please Wait')
        ajax.get('/api/bulk_assignments/'+grid_event.model.attributes.id+'/execute/test',function(data) {
            template = `
            <h5>The Following will be assigned to the "{{module.current_version.name}}" version of the "{{module.name}}" module:</h5>
            {{^assign_users.length}}
                <div class="alert alert-warning">No Users Will Be Assigned</div>
            {{/assign_users.length}}
            {{#assign_users}}
                {{first_name}} {{last_name}}, 
            {{/assign_users}}
            <h5>The Following have already been assigned to the "{{module.name}}" module and will be skipped:</h5>
            {{^skip_users.length}}
                <div class="alert alert-warning">No Users Will Be Skipped</div>
            {{/skip_users.length}}
            {{#skip_users}}
                {{first_name}} {{last_name}}, 
            {{/skip_users}}
            `;
            $('#adminModal .modal-title').html('Bulk Assignments (Test Run)')
            $('#adminModal .modal-body').html(gform.m(template,data));
            $('#adminModal').modal('show')    
        });
    }).on("model:run",function(grid_event) {
        toastr.info('Processing... Please Wait')
        ajax.get('/api/bulk_assignments/'+grid_event.model.attributes.id+'/execute',function(data) {
            template = `
            <h5>The Following were assigned to the "{{module.current_version.name}}" version of the "{{module.name}}" module:</h5>
            {{^assign_users.length}}
                <div class="alert alert-warning">No Users Were Assigned</div>
            {{/assign_users.length}}
            {{#assign_users}}
                {{first_name}} {{last_name}}, 
            {{/assign_users}}
            <h5>The Following were already assigned to the "{{module.name}}" module and were skipped:</h5>
            {{^skip_users.length}}
                <div class="alert alert-warning">No Users Were Skipped</div>
            {{/skip_users.length}}
            {{#skip_users}}
                {{first_name}} {{last_name}}, 
            {{/skip_users}}
            `;
            $('#adminModal .modal-title').html('Bulk Assignments')
            $('#adminModal .modal-body').html(gform.m(template,data));
            $('#adminModal').modal('show')    
        });
    }).on("model:configure_query",function(grid_event) {
        assignment_id = grid_event.model.attributes.id;
        assignment = grid_event.model.attributes.assignment || {};
        new gform(
            {
                "legend" : "Query Builder",
                "fields": [
                    {
                        "type":"smallcombo",
                        "options":"/api/modules",
                        "name":"module_id",
                        "label":"Module To Assign",
                        "format": {
                            "label": "{{name}}",
                            "value": "{{id}}"
                        }
                    },
                    {
                        type:"select",name:"date_due_format",label:"Due Date Format",options:[
                            {label:"Fixed Date",value:"fixed"},
                            {label:"Days From Today",value:"relative"}
                        ]
                    },
                    {type:"datetime", name:"date_due", label:"Due Date",format: {
                        input: "YYYY-MM-DD HH:mm:ss"
                        },show: [{type: "matches",name: "date_due_format",value: ["fixed"]}]
                    },
                    {type:"number",name:"days_from_now",label:"Due Date",show: [{type: "matches",name: "date_due_format",value: ["relative"]}]},     
                    {type:"checkbox", name:"auto", label:"Auto Assign?",help:"Use this option if you want auto-run this rule whenever group memberships or user attributes change"},
                    {type:"checkbox", name:"later_date", label:"Assign Later?",help:"Use this option if you want assign later with this bulk assignment rule"},
                    {type:"datetime", name:"later_assignment_date", label:"Auto Assign Date",format: {
                            input: "YYYY-MM-DD"
                        },show: [{type: "matches",name: "later_date",value: true}]
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
                                        "options":"/api/bulk_assignments/tables/columns"
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
                                                "options": ['=','!=','>','>=','<','<=','is_null','not_null','is_true','is_false','contains']
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
                                        show: [{type: "matches",name: "conditional",value: ['=','!=','>','>=','<','<=','contains']}]
                                    }
                                ],
                                "type": "fieldset"
                            }

                        ],
                        "type": "fieldset"
                    }
                ],
                "data": assignment
            }
        ).modal().on('save',function(form_event) {
            ajax.put('/api/bulk_assignments/'+assignment_id,{'assignment':form_event.form.get()},function(data) {
                grid_event.model.attributes.assignment = data
                form_event.form.trigger('close');
            });
        }).on('cancel',function(form_event) {
            form_event.form.trigger('close');
        });
    })
});

// Built-In Events:
//'edit','model:edit','model:edited','model:create','model:created','model:delete','model:deleted'


