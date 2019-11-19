ajax.get('/api/modules',function(data) {
    gdg = new GrapheneDataGrid({el:'#adminDataGrid',
    search: false,columns: false,upload:false,download:false,title:'Users',
    entries:[],
    actions:[
        {"name":"create"},
        '',
        {"name":"edit"},
        {"label":"Manage Versions","name":"manage_versions","min":1,"max":1,"type":"default"},
        {"label":"Manage Admins","name":"manage_admins","min":1,"max":1,"type":"default"},
        '',
        {"name":"delete"}
    ],
    count:4,
    schema:[
        {type:"hidden", name:"id"},
        {type:"text", name:"name", label:"Name"},
        {type:"textarea", name:"description", label:"Description Name"},
        {type:"text", name:"owner_user_id", label:"Owner"},
    ], data: data
    }).on("model:edited",function(grid_event) {
        ajax.put('/api/modules/'+grid_event.model.attributes.id,grid_event.model.attributes,function(data) {
            grid_event.model.attributes = data;
        });
    }).on("model:created",function(grid_event) {
        ajax.post('/api/modules',grid_event.model.attributes,function(data) {
            grid_event.model.attributes = data;
        });
    }).on("model:deleted",function(grid_event) {
        ajax.delete('/api/modules/'+grid_event.model.attributes.id,{},function(data) {});
    }).on("model:manage_versions",function(grid_event) {
        window.location = '/admin/modules/'+grid_event.model.attributes.id+'/versions';
    })
});

// Built-In Events:
//'edit','model:edit','model:edited','model:create','model:created','model:delete','model:deleted'


