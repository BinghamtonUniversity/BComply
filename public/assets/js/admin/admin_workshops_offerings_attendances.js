ajax.get('/api/workshops/'+ids[0]+'/offerings/'+ids[1]+'/attendances',function(data) {
    create_fields = [
        {type:"hidden", name:"workshop_id",label:"Workshop ID",value:ids[0]},
        {type:"hidden", name:"workshop_offering_id",label:"Workshop Offering ID",value:ids[1]},
        {type:"user", name:"user_id",required:true, label:"Attendee", template:"{{attributes.attendee.first_name}} {{attributes.attendee.last_name}}"},
        {type:"select", name:"attendance", label:"Attendance",options:[
            'registered', 'attended', 'unattended'
         ]},
        {type:"select", name:"status", label:"Status",options:[
           'not applicable', 'uncompleted', 'completed'
        ]},
      
    ];
    gdg = new GrapheneDataGrid({el:'#adminDataGrid',
    item_template: gform.stencils['table_row'],
    search: false,columns: false,upload:false,download:false,title:'Attendees',
    entries:[],
    actions:actions,
    count:20,
    schema:[
         {type:"hidden", name:"workshop_id",label:"Workshop ID",value:ids[0]},
        {type:"hidden", name:"workshop_offering_id",label:"Workshop Offering ID",value:ids[1]},
        {type:"user", name:"user_id",required:true, label:"Attendee", template:"{{attributes.attendee.first_name}} {{attributes.attendee.last_name}}"},
        {type:"select", name:"attendance", label:"Attendance",options:[
            'registered', 'attended', 'unattended'
         ]},
        {type:"select", name:"status", label:"Status",options:[
            'not applicable', 'uncompleted', 'completed'
        ]},

    ], data: data
    }).on("model:deleted",function(grid_event) {
        ajax.delete('/api/workshops/'+ids[0]+'/offerings/'+ids[1]+'/attendances/'+grid_event.model.attributes.id,{},function(data) {},function(data) {
            grid_event.model.undo();
           
        });
    }).on("model:edited",function(grid_event) {
        ajax.put('/api/workshops/'+ids[0]+'/offerings/'+ids[1]+'/attendances/'+grid_event.model.attributes.id,grid_event.model.attributes,function(data) {
            grid_event.model.update(data)
            // grid_event.model.update(data);
            // grid_event.model.draw();
        },function(data) {
            grid_event.model.undo();
        });
    }).on("model:created",function(grid_event) {
        ajax.post('/api/workshops/'+ids[0]+'/offerings/'+ids[1]+'/attendances',grid_event.model.attributes,function(data) {
           // debugger;
            grid_event.model.update(data)
            // grid_event.model.attributes = data;
            // grid_event.model.draw();
        },function(data) {
            grid_event.model.undo();
        });
    });

});
