ajax.get('/api/modules/'+id+'/permissions',function(data) {
    gdg = new GrapheneDataGrid({el:'#adminDataGrid',
    search: false,columns: false,upload:false,download:false,title:'Users',
    entries:[],
    actions:[
        {"name":"create","label":"New User/Module Permission"},
        '','',
        {"name":"delete","label":"Remove User/Module Permission"}
    ],
    count:4,
    schema:[
        {type:"hidden", name:"module_id"},
        {type:"hidden", name:"id"},
        {type:"user", name:"user_id", label:"User", template:"{{attributes.user.first_name}} {{attributes.user.last_name}}"},
        {type:"select", name:"permission", label:"Permission",options:[
            'manage','report','assign'
        ]},
    ], data: data
    }).on("model:created",function(grid_event) {
        console.log(id);
        ajax.put('/api/modules/'+id+'/permissions/',grid_event.model.attributes,function(data) {
            grid_event.model.attributes = data;
            grid_event.model.draw();
        },function(data) {
            grid_event.model.undo();
        });
    }).on("model:deleted",function(grid_event) {
        ajax.delete('/api/modules/'+id+'/permissions/'+grid_event.model.attributes.id,{},function(data) {},function(data) {
            grid_event.model.undo();
        });
    }).on("model:upload_module",function(grid_event) {
        toastr.error('This doesn\'t do anything!');
    })
});

// Built-In Events:
//'edit','model:edit','model:edited','model:create','model:created','model:delete','model:deleted'


