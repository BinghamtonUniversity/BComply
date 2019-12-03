ajax.get('/api/bulk_assignments',function(data) {
    gdg = new GrapheneDataGrid({el:'#adminDataGrid',
        search: false,columns: false,upload:false,download:false,title:'Bulk Assignments',
        entries:[],
        actions:[
            {"name":"create","label":"Create New Bulk Assignment"},
            '',
            {"name":"edit","label":"Edit Description"},
            {"label":"Configure Query","name":"configure_query","min":1,"max":1,"type":"default"},
            {"label":"Run Rule","name":"run_rule","min":1,"max":1,"type":"warning"},
            '',
            {"name":"delete","label":"Delete Bulk Assignment"}
        ],
        count:4,
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
    }).on("model:run_rule",function(grid_event) {
        // window.location = '/admin/bulk_assignments/'+grid_event.model.attributes.id+'/run';
            toastr.error("THIS DOES NOTHING");
    }).on("model:configure_query",function(grid_event) {
        assignment_id = grid_event.model.attributes.id;
        bulk_assignment = grid_event.model.attributes.bulk_assignment || {};
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
                                    }
                                ],
                                "type": "fieldset"
                            }

                        ],
                        "type": "fieldset"
                    }
                ],
                "data": bulk_assignment
            }
        ).modal().on('save',function(form_event) {
            ajax.put('/api/bulk_assignments/'+assignment_id,{'bulk_assignment':form_event.form.get()},function(data) {
                form_event.form.trigger('close');
            });
        }).on('cancel',function(form_event) {
            form_event.form.trigger('close');
        });
    })
});

// Built-In Events:
//'edit','model:edit','model:edited','model:create','model:created','model:delete','model:deleted'


