ajax.get('/api/reports/'+id+'/execute',function(data) {
    mySchema = _.map(data[0],function(a,b){
        return {"type":"text","name":b}
    })
    gdg = new GrapheneDataGrid({el:'#adminDataGrid',
        search: false,columns: false,upload:false,download:false,title:'Report',
        entries:[],
        actions:[],
        count:100,
        schema:mySchema, 
        data: data
    })
});

