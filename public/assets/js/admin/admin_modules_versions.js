ajax.get('/api/modules/'+id+'/versions',function(data) {
    gdg = new GrapheneDataGrid({el:'#adminDataGrid',
    item_template: gform.stencils['table_row'],
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
        {type:"text", name:"name", label:"Name",required:true},
        {type:"select", name:"type", label:"Type",options:[
            "articulate_tincan","youtube"
        ]},
    ], data: data
    }).on("model:edited",function(grid_event) {
        ajax.put('/api/modules/'+id+'/versions/'+grid_event.model.attributes.id,grid_event.model.attributes,function(data) {
            grid_event.model.update(data)
            // grid_event.model.attributes = data;
        },function(data) {
            grid_event.model.undo();
        });
    }).on("model:created",function(grid_event) {
        ajax.post('/api/modules/'+id+'/versions',grid_event.model.attributes,function(data) {
            grid_event.model.update(data)
            // grid_event.model.attributes = data;
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
        // debugger;
        body = `
        <form id="module_form_upload" method="post" enctype="multipart/form-data">
        <input type="file" name="zipfile" />
        <input type="submit" class="btn btn-primary" value="Upload" name="submit" />
        </form>
        `;
        $('#adminModal .modal-title').html('Module File Uploader')
        $('#adminModal .modal-body').html(body);
        $('#adminModal').modal('show')
            const url = '/api/modules/'+id+'/versions/'+grid_event.model.attributes.id+'/upload'
            const form = document.querySelector('#module_form_upload')
            form.addEventListener('submit', e => {
                e.preventDefault()
                ajax.get('/api/modules/'+id+'/versions/'+grid_event.model.attributes.id+'/exists',function(data) {
                    if (data.exists === true) {
                        if (confirm("!! WARNING !!\n\nThis file already exists.  \n\nAre you sure you want to overwrite it?  \n\n(Note: This action cannot be undone and 'in-progress' assignments will require users to start over from the beginning)")) {
                            upload_file(url+'?overwrite=true')
                        }
                    } else {
                        upload_file(url)
                    }
                },function(data) {});
            })
    }).on("model:configure",function(grid_event) {
        module_version_id = grid_event.model.attributes.id;
        module_version_type = grid_event.model.attributes.type;
        var form_fields = {};
        if (module_version_type === 'articulate_tincan') {
            form_fields = [
                {"type":"text","name":"filename","label":"Launch URL","value":"index_lms.html","required":true,"help":"This is the starting filename as specified when publishing the module in Articulate.  It is typically something like 'story_html5.html' or 'index_lms.html'"}
            ]
        } else if (module_version_type === 'youtube') {
            form_fields = [
                {"type":"text","name":"code","required":true,"label":"Youtube Code","help":"This is the string of characters at the end of the Youtube URL"},
                {"type":"checkbox","name":"controls","label":"Enable Controls"},
                {"type":"textarea","name":"instructions","label":"Instructions"}
            ]
        }
        new gform(
            {"fields":form_fields,
            "data":grid_event.model.attributes.reference,
            "actions":[{"type":"save"}]
            }
        ).modal().on('save',function(form_event) {
            if(form_event.form.validate()){
                ajax.put('/api/modules/' + id + '/versions/' + module_version_id, {"reference": form_event.form.get()}, function (data) {
                    grid_event.model.attributes.reference = data.reference;
                    form_event.form.trigger('close');
                });
            }
        });
    })
});

var upload_file = function(url) {
    const files = document.querySelector('[name=zipfile]').files
    const formData = new FormData()
    for (let i = 0; i < files.length; i++) {
        let file = files[i]
        formData.append('zipfile', file)
    }
    fetch(url, {
        method: 'POST',
        body: formData,
    }).
    then(response => {
        if(response.status==200){
            toastr.success("File Uploaded Successfully");
            $('#adminModal').modal('hide')
        }
        else{
            toastr.error(response.statusText)
        }
    })
}

// Built-In Events:
//'edit','model:edit','model:edited','model:create','model:created','model:delete','model:deleted'


