new gform(
    {
        "fields": [
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
                        "type": "select",
                        "label": "Column",
                        "name": "column",
                        "columns": "4",
                        "options":"/api/reports/tables/columns"
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
                                "options": [
                                    {
                                        "label": "contains",
                                        "value": "like"
                                    },
                                    {
                                        "label": ">",
                                        "value": ">"
                                    },
                                    {
                                        "label": ">=",
                                        "value": ">="
                                    },
                                    {
                                        "label": "=",
                                        "value": "="
                                    },
                                    {
                                        "label": "<=",
                                        "value": "<="
                                    },
                                    {
                                        "label": "<",
                                        "value": "<"
                                    }
                                ]
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
        "data": {
            "permissions": [
                "manage_users"
            ]
        }
    }
).modal().on('save',function(form_event) {
    ajax.post('/api/reports/query',form_event.form.get(),function(data) {
        form_event.form.trigger('close');
        mySchema = _.map(data[0],function(a,b){
            return {"type":"text","name":b}
        })
// Begin
gdg = new GrapheneDataGrid({el:'#adminDataGrid',
    search: false,columns: false,upload:false,download:false,title:'Users',
    entries:[],
    actions:[],
    count:4,
    schema:mySchema, data: data
    })
//End

    });
});

