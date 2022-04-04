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
        {
            "type":"textarea",
            "name":"notification",
            "id":"notification",
            "label":"Workshop Notification Template",
            "template": "{{attributes.notification}}",
            "value":
`{{user.first_name}} {{user.last_name}}<br>
<br>
This email serves as notification that you have been assigned the "{{workshop.name}}" training workshop, 
which is required to be completed by {{workshop.due_date}}.<br>
<br>
To complete this training, please utilize the following link: <a href="{{link}}">{{workshop.name}}</a>.`
        },
//         {
//             "name": "templates",
//             "type": "fieldset",
//             "editable":true,
//             "label": "Email Templates",
//             "fields": [
//                 {
//                     "type":"output",
//                     "name":"info_text",
//                     "label":false,
//                     "value":"<div class='alert alert-info'>Note: You may optionally leave templates blank to prevent triggering automated emails</div>",
//                     "parse":false,
//                 },
//                 {
//                     "type":"textarea",
//                     "name":"notification",
//                     "id":"notification",
//                     "label":"Workshop Notification Template",
//                     "template": "{{attributes.notification}}",
//                     "value":
// `{{user.first_name}} {{user.last_name}}<br>
// <br>
// This email serves as notification that you have been assigned the "{{offering.workshop.name}}" training workshop, 
// which is required to be completed by {{module.due_date}}.<br>
// <br>
// To complete this training, please utilize the following link: <a href="{{link}}">{{offering.workshop.name}}</a>.`
//                 },
//                 {
//                     "type":"textarea",
//                     "name":"reminders",
//                     "id":"reminders",
//                     "label":"Workshop Offering Reminder Template",
//                     "template": "{{attributes.reminders}}",
//                     "value":
// `{{user.first_name}} {{user.last_name}}<br>
// <br>
// This is a reminder that the "{{module.name}}" training module which was assigned to you
// on {{module.assignment_date}}, is due on {{module.due_date}}.<br>
// <br>
// To complete this training, please utilize the following link: <a href="{{link}}">{{module.name}}</a>.`
//                 },

//                 {
//                     "type":"textarea",
//                     "name":"completion",
//                     "id":"completion",
//                     "label":"Assignment Complation Template",
//                     "template": "{{attributes.completion}}",
//                     "value":
// `{{user.first_name}} {{user.last_name}}<br>
// <br>
// This email serves as confirmation that you have completed the "{{module.name}}" training module.<br>
// <br>
// You may view the confirmation certificate here: <a href="{{link}}">Certificate</a>`
//                 },
//                 {
//                     "type":"textarea",
//                     "name":"certificate",
//                     "id":"certificate",
//                     "label":"Completion Certificate Template",
//                     "template": "{{attributes.certificate}}",
//                     "value":
// `<h3>{{user.first_name}} {{user.last_name}}</h3> has completed<br>
// <b>{{module.name}}</b> module <b>{{module.version_name}}</b><br>
// at<br>
// <b>{{assignment.date_completed}}</b>`
//                 }
//             ]
//         },
      
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
            // Mail::to($user)->send(new AssignmentNotification($moduleAssignment,$user,$user_messages));
            //todo
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
