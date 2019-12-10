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
        {"label":"Make Current","name":"make_current","min":1,"max":1,"type":"warning"},
        '',
        {"name":"delete","label":"Delete Module Version"}
    ],
    count:20,
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
        //here
    }).on("model:make_current",function(grid_event) {
        ajax.put('/api/modules/'+id,{'module_version_id':grid_event.model.attributes.id},function(data) {
            toastr.success('"'+grid_event.model.attributes.name+'" is now the current version for this module')
        });
    }).on("model:upload_module",function(grid_event) {
        body = `
        <form id="module_form_upload" method="post" enctype="multipart/form-data">
        <input type="file" name="zipfile" />
        <input type="submit" value="Upload File" name="submit" />
        </form>
        `;
        $('#adminModal .modal-title').html('Dumb Uploader')
        $('#adminModal .modal-body').html(body);
        $('#adminModal').modal('show')
            // A bunch of upload stuff
            const url = '/api/modules/'+id+'/versions/'+grid_event.model.attributes.id+'/upload'
            const form = document.querySelector('#module_form_upload')
            form.addEventListener('submit', e => {
                debugger;
            e.preventDefault()
            const files = document.querySelector('[type=file]').files
            const formData = new FormData()
            for (let i = 0; i < files.length; i++) {
                let file = files[i]
                formData.append('files[]', file)
            }
            fetch(url, {
                method: 'POST',
                body: formData,
            }).then(response => {
                console.log(response)
            })
            })
            // end upload stuff
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


