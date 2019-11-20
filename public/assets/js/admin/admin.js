toastr.options = {
    "positionClass": "toast-bottom-right"
};

window.ajax = {};
window.ajax.get = function(url,callback_success,callback_error) {
    $.ajax({
        type: "GET",
        url: url,
        success:function(data) {
            if (typeof callback_success !== 'undefined') {callback_success(data);}
        },
        error:function(data) {
            if (typeof callback_error !== 'undefined') {callback_error(data);}
        }
    });
}
window.ajax.post = function(url,data,callback_success,callback_error) {
    $.ajax({
        type: "POST",
        url: url,
        contentType: "application/json",
        data: JSON.stringify(data),
        success:function(data) {
            toastr.success("Created Sucessfully")
            if (typeof callback_success !== 'undefined') {callback_success(data);}
        },
        error:function(data) {
            toastr.error("An Error Occurred During Creation")
            if (typeof callback_error !== 'undefined') {callback_error(data);}
        }
    });
}
window.ajax.put = function(url,data,callback_success,callback_error) {
    $.ajax({
        type: "PUT",
        url: url,
        contentType: "application/json",
        data: JSON.stringify(data),
        success:function(data) {
            toastr.success("Updated Sucessfully")
            if (typeof callback_success !== 'undefined') {callback_success(data);}
        },
        error:function(data) {
            toastr.error("An Error Occurred During Update")
            if (typeof callback_error !== 'undefined') {callback_error(data);}
        }

    });
}
window.ajax.delete = function(url,data,callback_success,callback_error) {
    $.ajax({
        type: "DELETE",
        url: url,
        contentType: "application/json",
        data: JSON.stringify(data),
        success:function(data) {
            toastr.success("Deleted Sucessfully")
            if (typeof callback_success !== 'undefined') {callback_success(data);}
        },
        error:function(data) {
            toastr.error("An Error Occurred During Deletion")
            if (typeof callback_error !== 'undefined') {callback_error(data);}
        }
    });
}

gform.types['user']= _.extend({}, gform.types['smallcombo'], {
    toString: function(name,display){
      if(!display){
        if(typeof this.combo !== 'undefined'){
          return '<dt>'+this.label+'</dt> <dd>'+(this.combo.innerText||'(empty)')+'</dd><hr>'
        }else{
          return '<dt>'+this.label+'</dt> <dd>'+(this.get()||'(empty)')+'</dd><hr>'
        }
      }else{
        if(typeof this.options !== 'undefined' && this.options.length){
          return _.find(this.options,{id:this.value})||this.value;
        }else{
          return this.value;
        }
      }
    },
    defaults:{strict:true,search:"/api/users/search/{{search}}{{value}}",format:{title:'{{{label}}}{{^label}}User{{/label}} <span class="text-success pull-right">{{value}}</span>',label:"{{first_name}}{{#last_name}} {{last_name}}{{/last_name}}",value:"{{id}}", display:'{{first_name}} {{last_name}}<div style="color:#aaa">{{email}}</div>'}}
  })
  