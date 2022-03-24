ajax.get('/api/workshops',function(data) {
    //debugger;
    create_fields = [
        {type:"hidden", name:"id"},
        {type:"checkbox", name:"public", label:"Public?","columns":6},
        {type:"number", name:"duration", label:"Duration (In Minutes)","columns":6},
        {type:"text", name:"name", label:"Name", columns:8, "required":true},
        {type:"text", name:"icon", label:"Icon", columns:4},
        {type:"textarea", name:"description", label:"Description Name"},
        {type:"user", name:"owner_id", label:"Owner", template:"{{attributes.owner.first_name}} {{attributes.owner.last_name}}", "required":true},
        {type:"textarea", name:"congif",label:"Config"},
      
    ];
    gdg = new GrapheneDataGrid({el:'#adminDataGrid',
        item_template: gform.stencils['table_row'],
        search: false,columns: false,upload:false,download:false,title:'Users',
        entries:[],
        actions:actions,
        count:20,
        create:{fields:create_fields},
        edit:{fields:create_fields},
        schema:[
            {type:"hidden", name:"id"},
            {type:"checkbox", name:"public", label:"Public?", template:"{{#attributes.public}}Public{{/attributes.public}}{{^attributes.public}}Private{{/attributes.public}}"},
            {type:"text", name:"name", label:"Name"},
            {type:"textarea", name:"description", label:"Description Name"},
            {type:"user", name:"owner_user_id", label:"Owner", template:"{{attributes.owner.first_name}} {{attributes.owner.last_name}}"},
        ],data: data
    }).on("model:edited",function(grid_event) {
        ajax.put('/api/workshops/'+grid_event.model.attributes.id,grid_event.model.attributes,function(data) {
            grid_event.model.update(data)
            // grid_event.model.update(data);
            // grid_event.model.draw();
        },function(data) {
            grid_event.model.undo();
        });
    }).on("model:created",function(grid_event) {
        ajax.post('/api/workshops',grid_event.model.attributes,function(data) {
            grid_event.model.update(data)
            // grid_event.model.attributes = data;
            // grid_event.model.draw();
        },function(data) {
            grid_event.model.undo();
            grid_event.model.draw();
        });
    }).on("model:deleted",function(grid_event) {
        ajax.delete('/api/workshops/'+grid_event.model.attributes.id,{},function(data) {},function(data) {
            grid_event.model.undo();
            grid_event.model.draw();
        });
    }).on("model:manage_offerings",function(grid_event) {
        window.location = '/admin/workshops/'+grid_event.model.attributes.id+'/offerings/';
    });
});
