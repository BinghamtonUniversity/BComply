ajax.get('/api/users/'+id+'/assignments',function(data) {
    gdg = new GrapheneDataGrid({el:'#adminDataGrid',
    search: false,columns: false,upload:false,download:false,title:'Users',
    entries:[],
    actions:[
        {"name":"create","label":"Add Module Assignment"},
        '',
        // {"name":"edit"},
        {"label":"View Report","name":"report","min":1,"max":1,"type":"default"},
        '',
        {"name":"delete","label":"Remove Module Assignment"}
    ],
    count:4,
    schema:[
        {type:"hidden", name:"id"},
        {type:"select", name:"module_version_id", label:"Module Version",options:"/api/module_versions",format:{label:"{{name}}", value:"{{id}}"}},
        {type:"datetime", name:"date_assigned", label:"Date Assigned",format: {
            input: "YYYY-MM-DD HH:mm:ss"
        }},
        {type:"datetime", name:"date_due", label:"Date Due",format: {
            input: "YYYY-MM-DD HH:mm:ss"
        }},
        {type:"text", name:"date_started", label:"Date Started", edit:false},
        {type:"text", name:"date_completed", label:"Date Completed", edit:false},
    ], data: data
    // Can't Update an assignment
    // }).on("model:edited",function(grid_event) {
    //     ajax.put('/api/users/'+id+'/assignments/'+grid_event.model.attributes.id,grid_event.model.attributes,function(data) {
    //         grid_event.model.attributes = data;
    //     });
    }).on("model:deleted",function(grid_event) {
        ajax.delete('/api/users/'+id+'/assignments/'+grid_event.model.attributes.id,{},function(data) {});
    }).on("model:created",function(grid_event) {
        ajax.post('/api/users/'+id+'/assignments',grid_event.model.attributes,function(data) {
            grid_event.model.attributes = data;
        });
    }).on("model:report",function(grid_event) {
        template = `
        <table class="table">
        <tbody>
        <tr><td>User</td><td>{{user.first_name}} {{user.last_name}}</td></tr>
        <tr><td>Module</td><td>{{version.name}}</td></tr>
        <tr><td>Status</td><td>{{status}}</td></tr>
        <tr><td>Score</td><td>{{score}}</td></tr>
        <tr><td>Duration</td><td>{{duration}}</td></tr>
        <tr><td>Date Assigned</td><td>{{date_assigned}}</td></tr>
        <tr><td>Date Due</td><td>{{date_due}}</td></tr>
        <tr><td>Date Started</td><td>{{date_started}}</td></tr>
        <tr><td>Date Completed</td><td>{{date_completed}}</td></tr>
        </tbody>
        </table>
        `;
        $('#adminModal .modal-title').html('Module Report')
        $('#adminModal .modal-body').html(gform.m(template,grid_event.model.attributes));
        $('#adminModal').modal('show')
    })
});

// Built-In Events:
//'edit','model:edit','model:edited','model:create','model:created','model:delete','model:deleted'


