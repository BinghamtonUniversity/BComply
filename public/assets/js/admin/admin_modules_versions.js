ajax.get('/api/modules/'+id+'/versions',function(data) {
    gdg = new GrapheneDataGrid({el:'#adminDataGrid',
    search: false,columns: false,upload:false,download:false,title:'Users',
    entries:[],
    actions:[
        {"name":"create","label":"Create New Module Version"},
        '',
        {"name":"edit","label":"Modify Module Version"},
        {"label":"Upload Module","name":"upload_module","min":1,"max":1,"type":"default"},
        {"label":"Configure","name":"configure","min":1,"max":1,"type":"default"},
        '',
        {"name":"delete","label":"Delete Module Version"}
    ],
    count:4,
    schema:[
        {type:"hidden", name:"id"},
        {type:"hidden", name:"module_id", value:id},
        {type:"text", name:"name", label:"Name"},
        {type:"select", name:"type", label:"Type",options:[
            "tincan","youtube"
        ]},
    ], data: data
    }).on("model:edited",function(grid_event) {
        ajax.put('/api/modules/'+id+'/versions/'+grid_event.model.attributes.id,grid_event.model.attributes,function(data) {
            grid_event.model.attributes = data;
        },function(data) {
            grid_event.model.undo();
        });
    }).on("model:created",function(grid_event) {
        ajax.post('/api/modules/'+id+'/versions/',grid_event.model.attributes,function(data) {
            grid_event.model.attributes = data;
        },function(data) {
            grid_event.model.undo();
        });
    }).on("model:deleted",function(grid_event) {
        ajax.delete('/api/modules/'+id+'/versions/'+grid_event.model.attributes.id,{},function(data) {},function(data) {
            grid_event.model.undo();
        });
    }).on("model:upload_module",function(grid_event) {
        toastr.error('This doesn\'t do anything!');
    }).on("model:configure",function(grid_event) {
        module_version_id = grid_event.model.attributes.id;
        module_version_type = grid_event.model.attributes.type;
        var form_fields = {};
        if (module_version_type === 'tincan') {
            form_fields = [
                {"type":"text","name":"filename","label":"File Name","value":"story.html","help":"This is the name of the html file"}
            ]
        } else if (module_version_type === 'youtube') {
            form_fields = [
                {"type":"text","name":"code","label":"Youtube Code","help":"This is the string of characters at the end of the Youtube URL"}
            ]
        }
        new gform(
            {"fields":form_fields,
            "data":grid_event.model.attributes.reference,
            "actions":[{"type":"save"}]
            }
        ).modal().on('save',function(form_event) {
            ajax.put('/api/modules/'+id+'/versions/'+module_version_id,{"reference":form_event.form.get()},function(data) {
                grid_event.model.attributes.reference = data.reference;
                form_event.form.trigger('close');
            });
        });
    })
});

// Built-In Events:
//'edit','model:edit','model:edited','model:create','model:created','model:delete','model:deleted'

