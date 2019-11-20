ajax.get('/api/groups/'+id+'/members',function(data) {
    gdg = new GrapheneDataGrid({el:'#adminDataGrid',
    search: false,columns: false,upload:false,download:false,title:'Users',
    entries:[],
    actions:[
        {"name":"create","label":"Add User to Group"},
        '','',
        {"name":"delete","label":"Remove User from Group"}
    ],
    count:4,
    schema:[
        {type:"hidden", name:"id"},
        {type:"user", name:"user_id", label:"User", template:"{{attributes.user.first_name}} {{attributes.user.last_name}}"},
    ], data: data
    }).on("model:created",function(grid_event) {
        ajax.post('/api/groups/'+id+'/members/',grid_event.model.attributes,function(data) {
            grid_event.model.attributes = data;
            grid_event.model.draw();
        });
    }).on("model:deleted",function(grid_event) {
        ajax.delete('/api/groups/'+id+'/members/'+grid_event.model.attributes.id,{},function(data) {});
    })
});

// Built-In Events:
//'edit','model:edit','model:edited','model:create','model:created','model:delete','model:deleted'


