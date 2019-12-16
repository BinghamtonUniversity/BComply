dashboard_template = `
<div class="row">
<duv class="col-sm-12">
<h1>Welcome {{first_name}} {{last_name}}</h1>
<h3>Permissions</h3>
<ul>
    {{#user_permissions}}
        <li>{{.}}</li>
    {{/user_permissions}}
</ul>
<h3>Group Memberships</h3>
<ul>
    {{#pivot_groups}}
        <li><a href="/admin/groups/{{id}}/members">{{name}}</a> ({{pivot.type}})</li>
    {{/pivot_groups}}
</ul>
<h3>Module Permissions</h3>
<ul>
    {{#pivot_module_permissions}}
        <li><a href="/admin/modules/{{id}}/versions">{{name}}</a> ({{pivot.permission}})</li>
    {{/pivot_module_permissions}}
</ul>
</div>
</div>
`;

ajax.get('/api/users/'+id,function(data) {
    $('#adminDataGrid').html(gform.m(dashboard_template,data));
});
