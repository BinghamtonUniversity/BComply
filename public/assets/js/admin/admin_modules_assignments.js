ajax.get('/api/modules/'+id+'/assignments',function(data) {
    gdg = new GrapheneDataGrid({el:'#adminDataGrid',
    search: false,columns: false,upload:false,download:false,title:'Users',
    entries:[],
    actions:[
        {"name":"create","label":"Add Module Assignment"},
        '',
        {"label":"Check as completed","name":"complete","min":1,"type":"danger"},
        {"label":"View Report","name":"report","min":1,"max":1,"type":"default"},
        '',
        {"name":"delete","label":"Remove Module Assignment"}
    ],
    count:20,
    schema:[
        {type:"hidden", name:"id"},
        {type:"text",name:"version", label:"Module Version", parse:false,show:false,template:"{{attributes.version.name}}"},
        {type:"user", name:"user_id", label:"User", template:"{{attributes.user.first_name}} {{attributes.user.last_name}}"},
        {type:"datetime", name:"date_assigned", label:"Date Assigned",parse:false,show:false,format: {
            input: "YYYY-MM-DD HH:mm:ss"
        }},
        {type:"datetime", name:"date_due", label:"Date Due",format: {
            input: "YYYY-MM-DD HH:mm:ss"
        }},
        {type:"text", parse:false,show:false, name:"date_started", label:"Date Started"},
        {type:"text", parse:false,show:false, name:"date_completed", label:"Date Completed"},
    ], data: data
    }).on("model:deleted",function(grid_event) {
        ajax.delete('/api/users/'+grid_event.model.attributes.user_id+'/assignments/'+grid_event.model.attributes.id,{},function(data) {},function(data) {
            grid_event.model.undo();
        });
    }).on("model:created",function(grid_event) {
        ajax.post('/api/users/'+grid_event.model.attributes.user_id+'/assignments/'+id,grid_event.model.attributes,function(data) {
            grid_event.model.attributes = data;
            grid_event.model.draw();
        },function(data) {
            grid_event.model.undo();
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
    }).on('model:complete',function(grid_event){
        ajax.put('/api/assignment/'+grid_event.model.attributes.id+'/complete',grid_event.model.attributes,function(data){
            grid_event.model.attributes = data;
            grid_event.model.draw();
        },function (err) {
            grid_event.model.undo();
            console.log(data.response)
        })
    })
});

// Built-In Events:
//'edit','model:edit','model:edited','model:create','model:created','model:delete','model:deleted'


