window.ajax = {};

window.ajax.get = function(url,callback_success) {
    $.ajax({
        type: "GET",
        url: url,
        success:callback_success
    });
}
window.ajax.post = function(url,data,callback_success) {
    $.ajax({
        type: "POST",
        url: url,
        contentType: "application/json",
        data: JSON.stringify(data),
        success:callback_success
    });
}
window.ajax.put = function(url,data,callback_success) {
    $.ajax({
        type: "PUT",
        url: url,
        contentType: "application/json",
        data: JSON.stringify(data),
        success:callback_success
    });
}
window.ajax.delete = function(url,data,callback_success) {
    $.ajax({
        type: "DELETE",
        url: url,
        contentType: "application/json",
        data: JSON.stringify(data),
        success:callback_success
    });
}