ajax.get('/api/workshops/'+id+'/files',function(data) {

    create_fields = [
        {type:"hidden", name:"id"},
        {type:"text", name:"name", label:"Name", columns:8, "required":true},
    ];
    gdg = new GrapheneDataGrid({el:'#adminDataGrid',
    item_template: gform.stencils['table_row'],
    search: false,columns: false,upload:false,download:false,title:'Attendees',
    entries:[],
    actions:actions,
    count:20,
    create:{fields:create_fields},
    edit:{fields:create_fields},
    schema:[
        {type:"hidden", name:"id"},
         {type:"text", name:"name", label:"Name", columns:8,},

    ], data: data
    }).on("model:deleted",function(grid_event) {
        ajax.delete('/api/workshops/'+id+'/files/'+grid_event.model.attributes.name,{},function(data) {},function(data) {
            grid_event.model.undo();
           
        });
    }).on("model:edited",function(grid_event) {
        ajax.put('/api/workshops/'+id+'/files/'+grid_event.model.attributes,grid_event.model.attributes,function(data) {
            grid_event.model.update(data)

        },function(data) {
            grid_event.model.undo();
        });
    })
    .on("model:upload_file",function(grid_event) {
        console.log("upload_file");
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
                const url = '/api/workshops/'+id+'/files/'+filename+'/upload'
                e.preventDefault()
                ajax.get('/api/workshops/'+id+'/files/'+filename+'/exists',function(data) {
                    if (data.exists === true) {
                        if (confirm("!! WARNING !!\n\nThis file already exists.  \n\nAre you sure you want to overwrite it?  \n\n(Note: This action cannot be undone and 'in-progress' workshops will require users to start over from the beginning)")) {
                        
                            upload_file(url+'?overwrite=true')
                        }
                    } else {
             
                        upload_file(url)
                    }
                    // ajax.get('/api/workshops/'+id+'/files',function(data) {
                    //     console.log("Ajax get");
                    //     grid_event.model.update(data);
                    // },function(data) {     
                    //     grid_event.model.undo();
                    //     grid_event.model.draw();
                    // }) 
                    //todo file name doesnt appear on the screen after uploading the file.
                 
                },function(data) {     
              
                });
               
            });
           
            
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
