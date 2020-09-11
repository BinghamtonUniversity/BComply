ajax.get('/api/groups',function(data) {
    gdg = new GrapheneDataGrid({el:'#adminDataGrid',
    item_template: gform.stencils['table_row'],
    search: false,columns: false,upload:false,download:false,title:'Groups',
    entries:[],
    actions:[
        {"name":"create","label":"New Group"},
        '',
        {"name":"edit","label":"Change Group Name"},
        {"label":"Manage Members","name":"manage_members","min":1,"max":1,"type":"default"},
        '',
        {"name":"delete","label":"Delete Group"}
    ],
    count:20,
    schema:[
        {type:"hidden", name:"id"},
        {type:"text", name:"name", label:"Name"},
    ], data: data
    }).on("model:edited",function(grid_event) {
        ajax.put('/api/groups/'+grid_event.model.attributes.id,grid_event.model.attributes,function(data) {
            grid_event.model.update(data)
            // grid_event.model.attributes = data;
        },function(data) {
            grid_event.model.undo();
        });
    }).on("model:created",function(grid_event) {
        ajax.post('/api/groups',grid_event.model.attributes,function(data) {
            grid_event.model.update(data)
            // grid_event.model.attributes = data;
        },function(data) {
            grid_event.model.undo();
        });
    }).on("model:deleted",function(grid_event) {
        ajax.delete('/api/groups/'+grid_event.model.attributes.id,{},function(data) {},function(data) {
            grid_event.model.undo();
        });
    }).on("model:manage_members",function(grid_event) {
        window.location = '/admin/groups/'+grid_event.model.attributes.id+'/members';
    })
});

// Built-In Events:
//'edit','model:edit','model:edited','model:create','model:created','model:delete','model:deleted'


