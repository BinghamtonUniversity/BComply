ajax.get('/api/workshops/'+id+'/offerings',function(data) {
    create_fields = [
        {type:"hidden", name:"workshop_id",label:"Workshop ID",value:id},
        
        {type:"user", name:"instructor_id",required:true, label:"Instructor", template:"{{attributes.instructor.first_name}} {{attributes.instructor.last_name}}"},
        {type:"number",name:"max_capacity",label:"Max Capacity"},
        {type:"text",name:"locations",label:"Locations"},
        {type:"datetime",name:"workshop_date",label:"Workshop Date",format: {
            input: "YYYY-MM-DD HH:mm:ss"
        }},
        {type:"select", name:"type", label:"Workshop Type",options:[
            'online','in-person'
        ]},
      
    ];
    gdg = new GrapheneDataGrid({el:'#adminDataGrid',
    item_template: gform.stencils['table_row'],
    search: false,columns: false,upload:false,download:false,title:'Instructors',
    entries:[],
    actions:actions,
    count:20,
    schema:[
        {type:"hidden", name:"id"},
        {type:"hidden", name:"workshop_id",label:"Workshop ID",value:id},
        
        {type:"user", name:"instructor_id",required:true, label:"Instructor", template:"{{attributes.instructor.first_name}} {{attributes.instructor.last_name}}"},
        {type:"number",name:"max_capacity",label:"Max Capacity"},
        {type:"text",name:"locations",label:"Locations"},
        {type:"datetime",name:"workshop_date",label:"Workshop Date",format: {
            input: "YYYY-MM-DD HH:mm:ss"
        }},
        {type:"select", name:"type", label:"Workshop Type",options:[
            'online','in-person'
        ]},

    ], data: data
    }).on("model:deleted",function(grid_event) {
        ajax.delete('/api/workshops/'+id+'/offerings/'+grid_event.model.attributes.id,{},function(data) {},function(data) {
            grid_event.model.undo();
           
        });
    }).on("model:created",function(grid_event) {
        ajax.post('/api/workshops/'+id+'/offerings',grid_event.model.attributes,function(data) {
           // debugger;
            grid_event.model.update(data)
            // grid_event.model.attributes = data;
            // grid_event.model.draw();
        },function(data) {
            grid_event.model.undo();
        });
    }).on("model:manage_attendance",function(grid_event) {
        window.location = '/admin/workshops/'+id+'/offerings/'+grid_event.model.attributes.id+"/attendance";
    });

});
