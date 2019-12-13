ajax.get('/api/modules',function(data) {
    fields = [
        {type:"hidden", name:"id"},
        {type:"checkbox", name:"public", label:"Public?"},
        {type:"text", name:"name", label:"Name"},
        {type:"textarea", name:"description", label:"Description Name"},
        {type:"user", name:"owner_user_id", label:"Owner", template:"{{attributes.owner.first_name}} {{attributes.owner.last_name}}"},
        {
            "type":"radio",
            "name":"reminders",
            "label":"Reminders Before Due Date",
            "multiple":true,
            "options":[
                {
                    "label":"1 Day",
                    "value":1
                },
                {
                    "label":"7 Days",
                    "value":7
                },
                {
                    "label":"14 Days",
                    "value":14
                },
                {
                    "label":"30 Days",
                    "value":30
                },
                {
                    "label":"60 Days",
                    "value":60
                }],
            "help":"Please select the days you would like to remind the users"
        },
        {type:"text", show:false, parse:false, name:"current", label:"Current Version", template:"{{attributes.current_version.name}}"},
    ];
    gdg = new GrapheneDataGrid({el:'#adminDataGrid',
        search: false,columns: false,upload:false,download:false,title:'Users',
        entries:[],
        actions:actions,
        count:20,
        create:{fields:fields},
        edit:{fields:fields},
        schema:[
            {type:"hidden", name:"id"},
            {type:"checkbox", name:"public", label:"Public?"},
            {type:"text", name:"name", label:"Name"},
            {type:"textarea", name:"description", label:"Description Name"},
            {type:"user", name:"owner_user_id", label:"Owner", template:"{{attributes.owner.first_name}} {{attributes.owner.last_name}}"},
            {
                "type":"text",
                "name":"reminders",
                "label":"Reminders",
            },
            {type:"text", show:false, parse:false, name:"current", label:"Current Version", template:"{{attributes.current_version.name}}"},
        ],data: data
    }).on("model:edited",function(grid_event) {
        ajax.put('/api/modules/'+grid_event.model.attributes.id,grid_event.model.attributes,function(data) {
            grid_event.model.attributes = data;
        },function(data) {
            grid_event.model.undo();
        });
    }).on("model:created",function(grid_event) {
        ajax.post('/api/modules',grid_event.model.attributes,function(data) {
            grid_event.model.attributes = data;
        },function(data) {
            grid_event.model.undo();
        });
    }).on("model:deleted",function(grid_event) {
        ajax.delete('/api/modules/'+grid_event.model.attributes.id,{},function(data) {},function(data) {
            grid_event.model.undo();
        });
    }).on("model:manage_versions",function(grid_event) {
        window.location = '/admin/modules/'+grid_event.model.attributes.id+'/versions';
    }).on("model:manage_admins",function(grid_event) {
        window.location = '/admin/modules/'+grid_event.model.attributes.id+'/permissions';
    }).on("model:manage_assignments",function(grid_event) {
        window.location = '/admin/modules/'+grid_event.model.attributes.id+'/assignments/';
    });
});
