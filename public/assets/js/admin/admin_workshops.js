ajax.get('/api/workshops',function(data) {
    //debugger;

    create_fields = [
        {type:"hidden", name:"id"},
        {type:"checkbox", name:"public", label:"Public?","columns":6},
        {type:"select", name:"duration", label:"Duration","columns":6,options:[
           '0:15','0:30','0:45','1:00','1:15','1:30','1:45','2:00','2:15','2:30','2:45','3:00','3:15','3:30','3:45','4:00'
        ]},
        //{type:"number", name:"duration", label:"Duration (In Minutes)","columns":6},
        {type:"text", name:"name", label:"Name", columns:8, "required":true},
        {type:"text", name:"icon", label:"Icon", columns:4},
        {type:"textarea", name:"description", label:"Description Name"},
        {type:"user", name:"owner_id", label:"Owner", template:"{{attributes.owner.first_name}} {{attributes.owner.last_name}}", "required":true},
        {
            "name": "config",
            "type": "fieldset",
            "editable":true,
            "label": "Config",
            "fields": [
                {
                    "type":"output",
                    "name":"info_text",
                    "label":false,
                    "value":"<div class='alert alert-info'>Note: You may optionally leave templates blank to prevent triggering automated emails</div>",
                    "parse":false,
                },
                {
                    "type":"textarea",
                    "name":"notification",
                    "id":"notification",
                    "label":"Workshop Notification Template",
                    "template": "{{attributes.config.notification}}",
                    "value":
                        `{{user.first_name}} {{user.last_name}}<br>
                        <br>
                        This email serves as notification that you have been assigned the "{{workshop.name}}" training workshop. 
                        This workshop starts on "{{workshop.workshop_date}}"<br>
                        <br>
                        To complete this training, please utilize the following link: <a href="{{link}}">{{workshop.name}}</a>.`
                },
                {
                    "type":"textarea",
                    "name":"reminders",
                    "id":"reminders",
                    "label":"Workshop Reminder Template",
                    "template": "{{attributes.config.reminders}}",
                    "value":
                        `{{user.first_name}} {{user.last_name}}<br>
                        <br>
                        This is a reminder that the "{{workshop.name}}" training module which was assigned to you.<br>
                        <br>
                        To complete this training, please utilize the following link: <a href="{{link}}">{{workshop.name}}</a>.`
                },

                {
                    "type":"textarea",
                    "name":"certificate",
                    "id":"certificate",
                    "label":"Workshop Certificate Template",
                    "template": "{{attributes.config.completion}}",
                    "value":
                        `{{user.first_name}} {{user.last_name}}<br>
                        <br>
                        This email serves as confirmation that you have completed the "{{workshop.name}}" training workshop.<br>
                        <br>
                        You may view the confirmation certificate here: <a href="{{link}}">Certificate</a>`
                },
                {
                    "type":"textarea",
                    "name":"completion",
                    "id":"completion",
                    "label":"Workshop Completion Template",
                    "template": "{{attributes.config.certificate}}",
                    "value":
                        `<h3>{{user.first_name}} {{user.last_name}}</h3> has completed<br>
                        <b>{{workshop.name}}</b> workshop <br>
                        at<br>
                        `
                }
            ]
        },
      
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
            {type:"text", name:"files", label:"Files"},
        ],data: data
    }).on("model:edited",function(grid_event) {
        const duration_formatted = grid_event.model.attributes.duration.split(':');
        const hourToMin = parseInt(duration_formatted[0])*60 + parseInt(duration_formatted[1]);
        grid_event.model.attributes.duration = hourToMin;
        ajax.put('/api/workshops/'+grid_event.model.attributes.id,grid_event.model.attributes,function(data) {
            grid_event.model.update(data)
            // grid_event.model.update(data);
            // grid_event.model.draw();
        },function(data) {
            grid_event.model.undo();
        });
    }).on("model:created",function(grid_event) {
        
        const duration_formatted = grid_event.model.attributes.duration.split(':');
        const hourToMin = parseInt(duration_formatted[0])*60 + parseInt(duration_formatted[1]);
        grid_event.model.attributes.duration = hourToMin;
        
        ajax.post('/api/workshops',grid_event.model.attributes,function(data) {
            grid_event.model.update(data)
            // grid_event.model.attributes = data;
            // grid_event.model.draw();
        },function(data) {
            grid_event.model.undo();
            grid_event.model.draw();
        });
    })
    .on("model:upload_file",function(grid_event) {
        // debugger;
        body = `
        <form id="workshop_file_upload" method="post" enctype="multipart/form-data">
      
        </br>
        <input type="file" name="file" id="file"/>
        </br>
        <input type="submit" class="btn btn-primary" value="Upload" name="submit" />
        </form>
        `;
        $('#adminModal .modal-title').html('Workshop File Uploader')
        $('#adminModal .modal-body').html(body);
        $('#adminModal').modal('show')

            const form = document.querySelector('#workshop_file_upload')
            
            form.addEventListener('submit', e => {
                const fake_path = document.getElementById('file').value
                const filename =fake_path.split("\\").pop();
                const url = '/api/workshops/'+grid_event.model.attributes.id+'/files/'+filename+'/upload'
                e.preventDefault()
                ajax.get('/api/workshops/'+grid_event.model.attributes.id+'/files/'+filename+'/exists',function(data) {
                    if (data.exists === true) {
                        if (confirm("!! WARNING !!\n\nThis file already exists.  \n\nAre you sure you want to overwrite it?  \n\n(Note: This action cannot be undone and 'in-progress' workshops will require users to start over from the beginning)")) {
                            upload_file(url+'?overwrite=true')
                        }
                    } else {
                        upload_file(url)
                    }
                    //todo file name doesnt appear on the screen after uploading the file.
                    grid_event.model.update(data);
                },function(data) {     
                    grid_event.model.undo();
                    grid_event.model.draw();
                });
            })
    })
    .on("model:deleted",function(grid_event) {
        ajax.delete('/api/workshops/'+grid_event.model.attributes.id,{},function(data) {},function(data) {
            grid_event.model.undo();
            grid_event.model.draw();
        });
    }).on("model:manage_offerings",function(grid_event) {
        window.location = '/admin/workshops/'+grid_event.model.attributes.id+'/offerings/';
    }).on("model:manage_files",function(grid_event) {
        window.location = '/admin/workshops/'+grid_event.model.attributes.id+'/files/';
    });
});
var upload_file = function(url) {
    
    toastr.info('Starting File Upload... Please Be Patient')
    const files = document.querySelector('[name=file]').files
    const formData = new FormData()
    for (let i = 0; i < files.length; i++) {
        let file = files[i]
        formData.append('file', file)
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