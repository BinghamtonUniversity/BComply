ajax.get('/api/instructor_workshops/',function(data) {
  
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
        {type:"number",name:"max_capacity",required:true,label:"Max Capacity"},
        {type:"text",name:"locations",required:true,label:"Locations"},
       
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
    }).on("click",function(grid_event) {     
        window.location = '/admin/workshops/'+ grid_event.model.attributes.workshop_id +'/offerings/'+grid_event.model.attributes.id+'/attendances';
      

    })
   
});
