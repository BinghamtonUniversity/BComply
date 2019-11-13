// import { request } from "https";

build_table = function(resource, options) {
    var options = options || {};
    if(typeof options.add == 'undefined'){
        options.add = function(options,model){
            this.app.post(resource, $.extend({id:id},model.attributes), function(context, data){
                 if(data.error) {
                    this.delete();
                    this.owner.draw();
                    if (data.error.message) {
                        toastr.error(data.error.message, 'ERROR');
                    } else {
                        toastr.error(data.error, 'ERROR');
                    }
                } else if (typeof data != 'object') {
                    this.delete();
                    this.owner.draw();
                    toastr.error('Creation Failed', 'ERROR')
                } else{
                    this.set(data);
                    this.owner.draw();
                    toastr.success('', 'Successfully Added');
                }
            }.bind(model, this),function(e){
				toastr.error(e.statusText, 'ERROR Creating.  Refresh and try again.');
	        })
        }.bind(this, options.default)
    }
    if(typeof options.edit == 'undefined'){
        options.edit = function(options,model){
            this.app.put(resource, $.extend({id:id},model.attributes), function(data){
                 if(data.error) {
                    if (data.error.message) {
                        toastr.error(data.error.message, 'ERROR');
                    } else {
                        toastr.error(data.error, 'ERROR');
                    }
                    this.undo();
                    this.owner.draw();                    
                } else if (typeof data != 'object') {
                    this.undo();
                    this.owner.draw();
                    toastr.error('Edit Failed', 'ERROR')
                } else{
                    toastr.success('', 'Successfully Edited');
                }
            }.bind(model,this),function(e){
				toastr.error(e.statusText, 'ERROR Editing.  Refresh and try again.');
	        })
        }.bind(this, options.default)
    }
    if(typeof options.delete == 'undefined'){
        options.delete = function(options, model){
            this.app.delete(resource, $.extend({id:id},model.attributes), function(data){
                 if(data.error) {
                    if (data.error.message) {
                        toastr.error(data.error.message, 'ERROR - Please Refresh');
                    } else {
                        toastr.error(data.error, 'ERROR - Please Refresh');
                    }
                } else{
                    toastr.success('', 'Successfully Deleted');
                }
            }.bind(model),function(e){
				toastr.error(e.statusText, 'ERROR deleting.  Refresh and try again.');
	        })
        }.bind(this, options.default)
    }
    if(typeof options.autoSize == 'undefined'){
        options.autoSize = 40;
    }
    options.container =  options.container || '#dataGrid';
    options.schema =  options.schema || adminFormFields[resource];
    options.data =  options.data || this.data[resource];

    if(resource === 'messages') {
        options.edit = false;
    }
    if(resource === 'notes') {
        options.edit = false;
    }

    if(resource === 'teams'){
        options.events = [
            {'name': 'members', 'label': '<i class="fa fa-users"></i> Members', callback: function(model){
                window.location.href = "/admin/teams/"+model.attributes.id+'/members';
            }, multiEdit: false},
            {'name': 'messages', 'label': '<i class="fa fa-comments"></i> Messages', callback: function(model){
                window.location.href = "/admin/teams/"+model.attributes.id+'/messages';
			}, multiEdit: false},
            {'name': 'notes', 'label': '<i class="fa fa-file"></i> Notes', callback: function(model){
                window.location.href = "/admin/teams/"+model.attributes.id+'/notes';
            }, multiEdit: false},
            {'name': 'params', 'label': '<i class="fa fa-file-medical"></i> Config', callback: function(model){
                window.open("/admin/teams/"+model.attributes.id+"/configuration",'_blank');
            }, multiEdit: false},
            {'name': 'reset', 'label': '<i class="fa fa-times"></i> Reset', callback: function(model){
                this.app.post(
                    'scenario_log', 
                    {team_id:model.attributes.id, state:_.find(this.data.scenarios,{id:model.attributes.scenario_id}).scenario, unique_id:this.data.user.unique_id}, 
                    function(){
                        toastr.success('Team reset Successfully');
                    }
                );
            }.bind(this), multiEdit: false},
        ]
    }    

    if(resource == 'users'){
        appcontext = this.app;
        options.events = [
            {'name': 'change_permissions', 'label': '<i class="fa fa-lock"></i> Change Permissions', callback: function(model){
                permissions_form = $().berry({
                    legend: 'Change Permissions',
                    name: 'permissions_form', 
                    inline:true,                
                    attributes: {user_id:model.attributes.id,permissions:Object.keys(model.attributes.permissions)},
                    fields: [{
                        type:'hidden',
                        value:model.attributes.id,
                        name:'user_id',
                        },{
                        label: 'Permissions',
                        type:"check_collection",
                        options:[
                            'manage_users','manage_user_permissions','manage_teams','manage_scenarios','manage_products','manage_prescribers','manage_solutions','manage_labs'
                        ],
                    }], actions: ['save']}).on('save', function() {
                        appcontext.post('update_permissions',permissions_form.toJSON(),function(data) {
                            this.set(data);
                            this.owner.draw();
                            toastr.success('', 'Successfully Updated Permissions');
                            permissions_form.destroy();
                        }.bind(this))
                    },model);
            }.bind(this), multiEdit: false}
        ]
    }   

    if(resource == 'scenarios'){
        options.events = [
            {'name': 'params', 'label': '<i class="fa fa-file-medical"></i> Configuration', callback: function(model){
                window.open("/admin/scenarios/"+model.attributes.id+"/configuration",'_blank');
			}, multiEdit: false},
		    {'name': 'duplicate', 'label': '<i class="fa fa-copy"></i> Duplicate', callback: function(model){
                $().berry({
                    flatten: false,
                    title: "Duplicate '"+model.attributes.name+"'",
                    fields: [{"label":"New Name","name":"name","required":true}],
                    name:'duplicate'
                }).on('save',function(){
                    if(Berries.duplicate.validate()){
                        this.owner.add({name:Berries.duplicate.toJSON().name,scenario:this.attributes.scenario})
                        Berries.duplicate.trigger('close');
                    }
                }.bind(model))
			}, multiEdit: false}
		]
    }
    return new berryTable(options)
}       

ajax.resources = {
    "users": "/users{{#request.id}}/{{request.id}}{{/request.id}}",
    "roles": "/roles{{#request.id}}/{{request.id}}{{/request.id}}",
    "teams": "/teams{{#request.id}}/{{request.id}}{{/request.id}}{{#request.team_id}}/{{request.team_id}}{{/request.team_id}}{{#request.resource}}/{{request.resource}}{{/request.resource}}{{#request.resource_id}}/{{request.resource_id}}{{/request.resource_id}}{{#request.user_id}}/{{request.user_id}}{{/request.user_id}}",
    "scenarios": "/scenarios{{#request.id}}/{{request.id}}{{/request.id}}",
    "products": "/library/products{{#request.id}}/{{request.id}}{{/request.id}}",
    "prescribers": "/library/prescribers{{#request.id}}/{{request.id}}{{/request.id}}",
    "solutions": "/library/solutions{{#request.id}}/{{request.id}}{{/request.id}}",
    "labs": "/library/labs{{#request.id}}/{{request.id}}{{/request.id}}",
    "scenario_log": "/teams/{{request.team_id}}/scenario_logs/{{user.unique_id}}",
    "update_permissions": "/users/{{request.user_id}}/permissions",
    "members": "/teams{{#request.id}}/{{request.id}}{{/request.id}}/members{{#request.user_id}}/{{request.user_id}}{{/request.user_id}}",
    "messages": "{{#request.team_id}}/teams/{{request.team_id}}/messages/{{request.id}}{{/request.team_id}}{{^request.team_id}}/teams/{{request.id}}/messages{{/request.team_id}}",
    "notes": "{{#request.team_id}}/teams/{{request.team_id}}/notes/{{request.id}}{{/request.team_id}}{{^request.team_id}}/teams/{{request.id}}/notes{{/request.team_id}}",
};

toastr.options = {
    "positionClass": "toast-bottom-right",
}