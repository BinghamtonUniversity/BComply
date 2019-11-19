ajax.get('/api/modules/'+id+'/versions',function(data) {
    gdg = new GrapheneDataGrid({el:'#adminDataGrid',
    search: false,columns: false,upload:false,download:false,title:'Users',
    entries:[],
    actions:[
        {"name":"create"},
        '',
        {"name":"edit"},
        {"label":"Upload Module","name":"upload_module","min":1,"max":1,"type":"default"},
        '',
        {"name":"delete"}
    ],
    count:4,
    schema:[
        {type:"hidden", name:"id"},
        {type:"hidden", name:"module_id", value:id},
        {type:"text", name:"name", label:"Name"},
        {type:"select", name:"type", label:"Type",options:[
            "tincan"
        ]},
    ], data: data
    }).on("model:edited",function(grid_event) {
        ajax.put('/api/modules/'+id+'/versions/'+grid_event.model.attributes.id,grid_event.model.attributes,function(data) {
            grid_event.model.attributes = data;
        });
    }).on("model:created",function(grid_event) {
        ajax.post('/api/modules/'+id+'/versions/',grid_event.model.attributes,function(data) {
            grid_event.model.attributes = data;
        });
    }).on("model:deleted",function(grid_event) {
        ajax.delete('/api/modules/'+id+'/versions/'+grid_event.model.attributes.id,{},function(data) {});
    }).on("model:upload_module",function(grid_event) {
        toastr.error('This doesn\'t do anything!');
    })
});

// Built-In Events:
//'edit','model:edit','model:edited','model:create','model:created','model:delete','model:deleted'


