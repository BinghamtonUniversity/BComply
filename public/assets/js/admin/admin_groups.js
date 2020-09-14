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
        {"label":"Bulk Add Members","name":"bulk_add","min":1,"max":1,"type":"warning"},
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
    }).on('model:bulk_add',function(grid_event){

        new gform({
            "legend":"Bulk Add",
            "name": "bulk_add",
            "fields": [
                {
                    "type": "textarea",
                    "label": "BNumbers",
                    "name": "b_numbers",
                    "showColumn": true,
                    "help":"Please enter a list of BNumbers. " +
                        "You can either use a \",\" (comma) to separate them or enter them in separate lines<br>" +
                        "Duplicates or existing group members will be ignored."
                }
            ]
        }).on('save',function(form_event){
            console.log(form_event.form.get())
            ajax.post('/api/groups/'+grid_event.model.attributes.id+'/users/bulk_add',form_event.form.get(),function(data) {
                form_event.form.trigger('close');
                template = `
                            <div class="alert alert-danger">
                                <h5>The Following BNumbers were ignored since they do not belong to an existing user:</h5>
                                <ul>
                                {{#b_numbers}}
                                    <li>{{.}}</li>
                                {{/b_numbers}}
                                </ul>
                            </div>
                            `;
                $('#adminModal .modal-title').html('Failed BNumbers')
                $('#adminModal .modal-body').html(gform.m(template,data));
                $('#adminModal').modal('show')
                // toastr.error(data);

            },function(data){
                // form_event.form.trigger('close');
            });
        }).modal()
    });
});

// Built-In Events:
//'edit','model:edit','model:edited','model:create','model:created','model:delete','model:deleted'


