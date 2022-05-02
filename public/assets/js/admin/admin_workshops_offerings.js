ajax.get('/api/workshops/'+id+'/offerings',function(data) {
    create_fields = [
        {type:"hidden", name:"workshop_id",label:"Workshop ID",value:id},
        
        {type:"user", name:"instructor_id",required:true, label:"Instructor", template:"{{attributes.instructor.first_name}} {{attributes.instructor.last_name}}"},
        {type:"number",name:"max_capacity",label:"Max Capacity"},
        {type:"text",name:"locations",label:"Locations"},
      
        {type:"select", name:"type", label:"Workshop Type",options:[
            'online','in-person'
        ]},
        {type:"checkbox", name:"is_multi_day", label:"Multiple Day?","columns":6,"show":[
            {
                "name": "is_recurring",
                "type": "matches",
                "value": [
                    false
                ]
            }
        ]},
        {type:"checkbox", name:"is_recurring", label:"Recurring","columns":12,"show":[ {
            "name": "is_multi_day",
            "type": "matches",
            "value": [
                false
            ]
        }]},
        {type:"datetime",name:"workshop_date",label:"Workshop Date",required:true, format: {
            input: "YYYY-MM-DD HH:mm:ss"
        }, "show": [
            {
                "name": "is_multi_day",
                "type": "matches",
                "value": [
                    false
                ]
            },
            {
                "name": "is_recurring",
                "type": "matches",
                "value": [
                    false
                ]
            }
        ],
         
    },

        {type:"datetime",name:"multi_days",label:"Workshop Dates",required:true,format: {
            input: "YYYY-MM-DD HH:mm:ss"
        }, "show": [
            {
                "name": "is_multi_day",
                "type": "matches",
                "value": [
                    true
                ]
            }
        ],
        "array": 50  },
        // {type:"select",name:"recurrence",label:"Reccurence",options:[
        //     'Daily','Weekly','Monthly'
        // ] ,required:true, "show": [
        //     {
        //         "name": "is_recurring",
        //         "type": "matches",
        //         "value": [
        //             true
        //         ]
        //     }
        // ],
        // },
        //todo duplicate for week month year
        // {type:"select",name:"repeat",label:"Repeat every",options:[
        //     '1','2','3','4','5','6','7'
        // ] ,required:true, "show": [
        //     {
        //         "name": "recurrence",
        //         "type": "matches",
        //         "value": [
        //             "Daily"
        //         ]
        //     },
        //     {
        //         "name": "is_recurring",
        //         "type": "matches",
        //         "value": [
        //             true
        //         ]
        //     }
        // ],
        // },
        // {
        //     "type":"radio",
        //     "name":"repeat_every_weekly",
        //     "label":"Repeat every",
        //     "multiple":true,
        //     "columns":6,
        //     "options":[
        //         {
        //             "label":"Sunday",
        //             "value":0
        //         },
        //         {
        //             "label":"Monday",
        //             "value":1
        //         },
        //         {
        //             "label":"Tuesday",
        //             "value":2
        //         },
        //         {
        //             "label":"Wednesday",
        //             "value":3
        //         },
        //         {
        //             "label":"Thursday",
        //             "value":4
        //         },
        //         {
        //             "label":"Friday",
        //             "value":5
        //         },
        //         {
        //             "label":"Saturday",
        //             "value":6
        //         }],
        //     "show": [
        //         {
        //             "name": "recurrence",
        //             "type": "matches",
        //             "value": [
        //                 "Weekly"
        //             ]
        //         },
        //         {
        //             "name": "is_recurring",
        //             "type": "matches",
        //             "value": [
        //                 true
        //             ]
        //         }
        //     ],
        // },
        // {
        //     "type":"radio",
        //     "name":"repeat_every_monthly",
        //     "label":"Repeat every",
        //     "multiple":true,
        //     "columns":3,
        //     "options":[
        //         {
        //             "label":"1st",
        //             "value":0
        //         },
        //         {
        //             "label":"2nd",
        //             "value":1
        //         },
        //         {
        //             "label":"3rd",
        //             "value":2
        //         },
        //         {
        //             "label":"4th",
        //             "value":3
        //         },
        //         {
        //             "label":"5th",
        //             "value":4
        //         },
        //         {
        //             "label":"6th",
        //             "value":5
        //         },
        //         {
        //             "label":"7th",
        //             "value":6
        //         },
        //         {
        //             "label":"8th",
        //             "value":7
        //         },
        //         {
        //             "label":"9th",
        //             "value":8
        //         },
        //         {
        //             "label":"10th",
        //             "value":9
        //         }],
        //     "show": [
        //         {
        //             "name": "recurrence",
        //             "type": "matches",
        //             "value": [
        //                 "Monthly"
        //             ]
        //         },
        //         {
        //             "name": "is_recurring",
        //             "type": "matches",
        //             "value": [
        //                 true
        //             ]
        //         }
        //     ],
        // },

        //todo 
        {
            "type":"radio",
            "name":"repeat_every_placement",
            "label":"Repeat every",
            "multiple":true,
            "columns":3,
            "options":[
                {
                    "label":"1st",
                    "value":0
                },
                {
                    "label":"2nd",
                    "value":1
                },
                {
                    "label":"3rd",
                    "value":2
                },
                {
                    "label":"4th",
                    "value":3
                },
                {
                    "label":"5th",
                    "value":4
                }
                ],
            "show": [
               
                {
                    "name": "is_recurring",
                    "type": "matches",
                    "value": [
                        true
                    ]
                }
            ],
        },
        {
            "type":"radio",
            "name":"repeat_every_on",
            "label":"On",
            "multiple":true,
            "columns":3,
            "options":[
                {
                    "label":"Monday",
                    "value":1
                },
                {
                    "label":"Tuesday",
                    "value":2
                },
                {
                    "label":"Wednesday",
                    "value":3
                },
                {
                    "label":"Thursday",
                    "value":4
                },
                {
                    "label":"Friday",
                    "value":5
                }
                ],
            "show": [
               
                {
                    "name": "is_recurring",
                    "type": "matches",
                    "value": [
                        true
                    ]
                }
            ],
        },
        //todo
        {type:"datetime",name:"recurring_start_date",label:"Start Date",required:true, format: {
            input: "YYYY-MM-DD HH:mm:ss"
        },"show": [
            {
                "name": "is_recurring",
                "type": "matches",
                "value": [
                    true
                ]
            }
        ],
        },
        {type:"datetime",name:"recurring_end_date",label:"End Date",required:true, format: {
            input: "YYYY-MM-DD HH:mm:ss"
        },"show": [
            {
                "name": "is_recurring",
                "type": "matches",
                "value": [
                    true
                ]
            }
        ],
        },
      
    ];
    gdg = new GrapheneDataGrid({el:'#adminDataGrid',
    item_template: gform.stencils['table_row'],
    search: false,columns: false,upload:false,download:false,title:'Instructors',
    entries:[],
    actions:actions,
    create:{fields:create_fields},
    edit:{fields:create_fields},
    count:20,
    schema:[
        {type:"hidden", name:"id"},
        {type:"hidden", name:"workshop_id",label:"Workshop ID",value:id},
        
        {type:"user", name:"instructor_id",required:true, label:"Instructor", template:"{{attributes.instructor.first_name}} {{attributes.instructor.last_name}}"},
        {type:"number",name:"max_capacity",label:"Max Capacity"},
        {type:"text",name:"locations",label:"Locations"},
       
        {type:"select", name:"type", label:"Workshop Type",options:[
            'online','in-person'
        ]},
        {type:"checkbox", name:"is_multi_day", label:"Multiple Day?","columns":6},
        {type:"datetime",name:"workshop_date",label:"Workshop Date",format: {
            input: "YYYY-MM-DD HH:mm:ss"
        }},
        {type:"datetime",name:"multi_days",label:"Workshop Dates",format: {
            input: "YYYY-MM-DD HH:mm:ss"
        }},

    ], data: data
    }).on("model:deleted",function(grid_event) {
        ajax.delete('/api/workshops/'+id+'/offerings/'+grid_event.model.attributes.id,{},function(data) {},function(data) {
            grid_event.model.undo();
           
        });
    }).on("model:edited",function(grid_event) {
        if(grid_event.model.attributes.is_multi_day){
           
            grid_event.model.attributes.workshop_date = grid_event.model.attributes.multi_days[0]
            var myJsonString =  grid_event.model.attributes.multi_days;
            grid_event.model.attributes.multi_days =myJsonString;
 
        }
        ajax.put('/api/workshops/'+id+'/offerings/'+grid_event.model.attributes.id,grid_event.model.attributes,function(data) {
            grid_event.model.update(data)
            // grid_event.model.update(data);
            // grid_event.model.draw();
        },function(data) {
            grid_event.model.undo();
        });
    }).on("model:created",function(grid_event) {
        if(grid_event.model.attributes.is_multi_day){
            
            grid_event.model.attributes.workshop_date = grid_event.model.attributes.multi_days[0]
            var myJsonString =  grid_event.model.attributes.multi_days;
            grid_event.model.attributes.multi_days =myJsonString;
        
        }
        ajax.post('/api/workshops/'+id+'/offerings',grid_event.model.attributes,function(data) {
           // debugger;
            grid_event.model.update(data)
            // grid_event.model.attributes = data;
            // grid_event.model.draw();
        },function(data) {
            grid_event.model.undo();
        });
    })
    // .on("model:manage_attendance",function(grid_event) {
    //     window.location = '/admin/offerings/'+grid_event.model.attributes.id+"/attendances";
    // });
    //TODO might be replaced with above
    .on("model:manage_attendance",function(grid_event) {
        window.location = '/admin/workshops/'+id+'/offerings/'+grid_event.model.attributes.id+"/attendances";
    });
});
