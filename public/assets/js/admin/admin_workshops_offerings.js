ajax.get('/api/workshops/'+id+'/offerings',function(data) {
    create_fields = [
        {type:"hidden", name:"workshop_id",label:"Workshop ID",value:id},
        
        {type:"user", name:"instructor_id",required:true, label:"Instructor", template:"{{attributes.instructor.first_name}} {{attributes.instructor.last_name}}"},
        {type:"number",name:"max_capacity",label:"Max Capacity"},
        {type:"text",name:"locations",label:"Locations"},
      
        {type:"select", name:"type", label:"Workshop Type",options:[
            'online','in-person'
        ]},
        {type:"checkbox", name:"is_multi_day", label:"Multiple Day?","columns":6},
        {type:"datetime",name:"workshop_date",label:"Workshop Date",required:true, format: {
            input: "YYYY-MM-DD HH:mm:ss"
        }, "show": [
            {
                "name": "is_multi_day",
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
