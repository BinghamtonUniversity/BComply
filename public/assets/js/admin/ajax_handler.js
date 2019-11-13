ajax = {}
ajax.resources = {} // Set this externally
ajax.handler = function(name, data, callback_success, callback_fail, verb){
    if (verb === 'GET') {
        var content_type = 'application/x-www-form-urlencoded; charset=UTF-8'
        var data_to_send = data;
    } else {
        var content_type = 'application/json'
        var data_to_send = JSON.stringify(data);
    }
    send_data = {request:data}
    url = '/api'+gform.renderString(ajax.resources[name],send_data)
    $.ajax({
        url: url,
        type: verb,
        data:data_to_send,
        contentType: content_type,
        error: function (data) {
            toastr.error(data.statusText, 'ERROR')
            callback_fail.call(this,data)
        }.bind(this),
        success: function (data) {
            callback_success.call(this,data)
        }.bind(this)
    });
}.bind(this)

ajax.get = function(name, data, callback_success, callback_fail) {
    ajax.handler(name, data, callback_success, callback_fail, 'GET')
}.bind(this)
ajax.post = function(name, data, callback_success, callback_fail) {
    ajax.handler(name, data, callback_success, callback_fail, 'POST')
}.bind(this)
ajax.put = function(name, data, callback_success, callback_fail) {
    ajax.handler(name, data, callback_success, callback_fail, 'PUT')
}.bind(this)
ajax.delete = function(name, data, callback_success, callback_fail) {
    ajax.handler(name, data, callback_success, callback_fail, 'DELETE')
}.bind(this)