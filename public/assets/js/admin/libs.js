var QueryStringToHash = function(query) {
    var query_string = {};
    var vars = query.split("&");
    for (var i=0;i<vars.length;i++) {
      var pair = vars[i].split("=");
      pair[0] = decodeURIComponent(pair[0]);
      pair[1] = decodeURIComponent((pair[1] || "").split('+').join(' '));
        // If first entry with this name
      if (typeof query_string[pair[0]] === "undefined") {
        query_string[pair[0]] = pair[1];
        // If second entry with this name
      } else if (typeof query_string[pair[0]] === "string") {
        var arr = [ query_string[pair[0]], pair[1] ];
        query_string[pair[0]] = arr;
        // If third or later entry with this name
      } else {
        query_string[pair[0]].push(pair[1]);
      }
    } 
    return query_string;
  };
  
  function customRender(content, scope) {
    // return content+'hrell';
    scope = scope||{};
    var myRegexp = /\[(.*?)\]/g;
    var match = myRegexp.exec(content);
    var response = JSON.parse( JSON.stringify( content || "" ) );
    var temp;
  
    while (match != null) {
        try{
            var format = 'L';
            if(match[1].indexOf(':')>=0){
                var parts = match[1].split(':');
                format = parts[1];
                match[1] = parts[0];
            }
            match[1] = match[1].replace(/Admission/gi, '{{patient_information.admitted_on}}')
            // .replace(/DOB/gi, '{{patient_information.date_of_birth}}');
            .replace(/\{\{&gt;/gi, '{{>');
            match[1] = customRender(Hogan.compile(match[1]).render(scope.data, templates), scope);	
  
            var converted = {};
            if(match[1].indexOf('&')>=0){
                 var parts = match[1].split('&');
                 converted = moment(Date.past(parts[0].substr(0,parts[0].length-1))).subtract(parts[1], 'years').format();
            }
            else{
                converted = Date.create(match[1]);
            }
          if((typeof converted == "string" || converted instanceof Date) && converted !== "Invalid date" && converted !== "Invalid Date"){
             temp = moment(converted).format(format);
             if(converted == "Invalid Date"){
                 temp = match[1];
             }
          }else{
             temp = match[1];
          }
      }catch(e){}
  
      response = response.replace(match[0], temp || match[0]);
      match = myRegexp.exec(content);
    }
      return response;
  }
  
  
  
  /* EHR Berry Extensions */
  templates["berry__addons"] = new Hogan.Template({code: function (c,p,i) { var t=this;t.b(i=i||"");t.b("<!-- <span class=\"help-inline\"> ");t.b(t.t(t.f("help",c,p,0)));t.b("</span> -->");t.b("\n" + i);t.b("<span class=\"font-xs text-danger\" style=\"display:block;\"></span>");return t.fl(); },partials: {}, subs: {  }});
  templates["berry__label"] = new Hogan.Template({code: function (c,p,i) { var t=this;t.b(i=i||"");if(!t.s(t.f("hideLabel",c,p,1),c,p,1,0,0,"")){t.b("	");if(t.s(t.f("label",c,p,1),c,p,0,26,327,"{{ }}")){t.rs(c,p,function(c,p,t){t.b("<label for=\"");t.b(t.v(t.f("guid",c,p,0)));t.b("\" ");if(t.s(t.f("inline",c,p,1),c,p,0,59,82,"{{ }}")){t.rs(c,p,function(c,p,t){t.b("style=\"text-align:left\"");});c.pop();}t.b(" class=\"control-label col-md-");if(t.s(t.f("inline",c,p,1),c,p,0,133,135,"{{ }}")){t.rs(c,p,function(c,p,t){t.b("12");});c.pop();}if(!t.s(t.f("inline",c,p,1),c,p,1,0,0,"")){t.b("4");};t.b("\">");t.b("\n" + i);t.b("  ");t.b(t.t(t.f("label",c,p,0)));t.b(":");if(t.s(t.f("required",c,p,1),c,p,0,199,233,"{{ }}")){t.rs(c,p,function(c,p,t){t.b("<span class=\"text-danger\">*</span>");});c.pop();}t.b("\n" + i);t.b("  <div style=\"font-weight:normal;font-style: italic;\"> ");t.b(t.t(t.f("help",c,p,0)));t.b("</div>");t.b("\n" + i);t.b("</label>");});c.pop();}t.b("\n" + i);};return t.fl(); },partials: {}, subs: {  }});
  templates["berry_check_collection"] = new Hogan.Template({code: function (c,p,i) { var t=this;t.b(i=i||"");t.b("<div class=\"row clearfix form-group ");t.b(t.v(t.f("modifiers",c,p,0)));t.b(" ");if(t.s(t.d("multiple.duplicate",c,p,1),c,p,0,73,136,"{{ }}")){t.rs(c,p,function(c,p,t){t.b("dupable\" data-min=\"");t.b(t.v(t.d("multiple.min",c,p,0)));t.b("\" data-max=\"");t.b(t.v(t.d("multiple.max",c,p,0)));});c.pop();}t.b("\" name=\"");t.b(t.v(t.f("name",c,p,0)));t.b("\" data-type=\"");t.b(t.v(t.f("type",c,p,0)));t.b("\">");t.b("\n" + i);t.b(t.rp("<berry__label0",c,p,"	"));if(t.s(t.d("multiple.duplicate",c,p,1),c,p,0,242,392,"{{ }}")){t.rs(c,p,function(c,p,t){t.b("	<div class=\"duplicate add btn btn-default\"><i class=\"fa fa-plus\"></i></div>");t.b("\n" + i);t.b("	<div class=\"btn btn-default remove\"><i class=\"fa fa-minus\"></i></div>");t.b("\n" + i);});c.pop();}if(t.s(t.f("label",c,p,1),c,p,0,427,522,"{{ }}")){t.rs(c,p,function(c,p,t){t.b("	");if(t.s(t.f("inline",c,p,1),c,p,0,440,463,"{{ }}")){t.rs(c,p,function(c,p,t){t.b("<div class=\"col-md-12\">");});c.pop();}t.b("\n" + i);t.b("	");if(!t.s(t.f("inline",c,p,1),c,p,1,0,0,"")){t.b("<div class=\"col-md-8\">");};t.b("\n" + i);});c.pop();}if(!t.s(t.f("label",c,p,1),c,p,1,0,0,"")){t.b("\n" + i);t.b("	<div class=\"col-md-4\"></div>");t.b("\n" + i);t.b("	<div class=\"col-md-8\">");t.b("\n" + i);};t.b("		");if(t.s(t.f("pre",c,p,1),c,p,0,622,695,"{{ }}")){t.rs(c,p,function(c,p,t){t.b("<div class=\"input-group\"><span class=\"input-group-addon\">");t.b(t.t(t.f("pre",c,p,0)));t.b("</span>");});c.pop();}t.b("\n" + i);t.b("		");if(!t.s(t.f("pre",c,p,1),c,p,1,0,0,"")){if(t.s(t.f("post",c,p,1),c,p,0,723,748,"{{ }}")){t.rs(c,p,function(c,p,t){t.b("<div class=\"input-group\">");});c.pop();}};t.b("\n" + i);t.b("<div class=\"row\" style=\"margin-top:1px\">");t.b("\n" + i);if(t.s(t.f("options",c,p,1),c,p,0,824,1173,"{{ }}")){t.rs(c,p,function(c,p,t){t.b("\n" + i);t.b("<div class=\"checkbox col-md-4\">");t.b("\n");t.b("\n");t.b("\n" + i);t.b("						<label class=\"");t.b(t.v(t.f("alt-display",c,p,0)));t.b("\">");t.b("\n" + i);t.b("							<input name=\"");t.b(t.v(t.f("value",c,p,0)));t.b("\" type=\"checkbox\" ");if(!t.s(t.f("isEnabled",c,p,1),c,p,1,0,0,"")){t.b("readonly");};t.b(" ");if(t.s(t.f("selected",c,p,1),c,p,0,995,1010,"{{ }}")){t.rs(c,p,function(c,p,t){t.b("checked=checked");});c.pop();}t.b(">");if(t.s(t.f("container",c,p,1),c,p,0,1038,1125,"{{ }}")){t.rs(c,p,function(c,p,t){t.b("<");t.b(t.v(t.f("container",c,p,0)));t.b(" style=\"position:relative;display:inline-block\">");t.b(t.v(t.f("label",c,p,0)));t.b("</");t.b(t.v(t.f("container",c,p,0)));t.b(">");});c.pop();}t.b("\n" + i);t.b("						</label>");t.b("\n" + i);t.b("</div>");t.b("\n");t.b("\n" + i);});c.pop();}t.b("									</div>");t.b("\n");t.b("\n" + i);t.b("		");if(t.s(t.f("post",c,p,1),c,p,0,1214,1269,"{{ }}")){t.rs(c,p,function(c,p,t){t.b("<span class=\"input-group-addon\">");t.b(t.t(t.f("post",c,p,0)));t.b("</span></div>");});c.pop();}t.b("\n" + i);t.b("		");if(!t.s(t.f("post",c,p,1),c,p,1,0,0,"")){if(t.s(t.f("pre",c,p,1),c,p,0,1298,1304,"{{ }}")){t.rs(c,p,function(c,p,t){t.b("</div>");});c.pop();}};t.b("\n" + i);t.b(t.rp("<berry__addons1",c,p,"		"));t.b("	</div>");t.b("\n" + i);t.b("</div>");return t.fl(); },partials: {"<berry__label0":{name:"berry__label", partials: {}, subs: {  }},"<berry__addons1":{name:"berry__addons", partials: {}, subs: {  }}}, subs: {  }});
  templates["berry_qrcode"] = new Hogan.Template({code: function (c,p,i) { var t=this;t.b(i=i||"");t.b("<div class=\"row clearfix form-group ");t.b(t.v(t.f("modifiers",c,p,0)));t.b(" ");if(t.s(t.d("multiple.duplicate",c,p,1),c,p,0,73,136,"{{ }}")){t.rs(c,p,function(c,p,t){t.b("dupable\" data-min=\"");t.b(t.v(t.d("multiple.min",c,p,0)));t.b("\" data-max=\"");t.b(t.v(t.d("multiple.max",c,p,0)));});c.pop();}t.b("\" name=\"");t.b(t.v(t.f("name",c,p,0)));t.b("\" data-type=\"file\">");t.b("\n" + i);t.b(t.rp("<berry__label0",c,p,"	"));if(t.s(t.d("multiple.duplicate",c,p,1),c,p,0,238,388,"{{ }}")){t.rs(c,p,function(c,p,t){t.b("	<div class=\"duplicate add btn btn-default\"><i class=\"fa fa-plus\"></i></div>");t.b("\n" + i);t.b("	<div class=\"btn btn-default remove\"><i class=\"fa fa-minus\"></i></div>");t.b("\n" + i);});c.pop();}if(t.s(t.f("label",c,p,1),c,p,0,423,518,"{{ }}")){t.rs(c,p,function(c,p,t){t.b("	");if(t.s(t.f("inline",c,p,1),c,p,0,436,459,"{{ }}")){t.rs(c,p,function(c,p,t){t.b("<div class=\"col-md-12\">");});c.pop();}t.b("\n" + i);t.b("	");if(!t.s(t.f("inline",c,p,1),c,p,1,0,0,"")){t.b("<div class=\"col-md-8\">");};t.b("\n" + i);});c.pop();}if(!t.s(t.f("label",c,p,1),c,p,1,0,0,"")){t.b("	<div class=\"col-md-12\">");t.b("\n" + i);};t.b("		");if(t.s(t.f("pre",c,p,1),c,p,0,588,671,"{{ }}")){t.rs(c,p,function(c,p,t){t.b("<div class=\"input-group col-xs-12\"><span class=\"input-group-addon\">");t.b(t.t(t.f("pre",c,p,0)));t.b("</span>");});c.pop();}t.b("\n" + i);t.b("    ");if(!t.s(t.f("pre",c,p,1),c,p,1,0,0,"")){if(t.s(t.f("post",c,p,1),c,p,0,701,726,"{{ }}")){t.rs(c,p,function(c,p,t){t.b("<div class=\"input-group\">");});c.pop();}};t.b("\n" + i);t.b("		");if(!t.s(t.f("success",c,p,1),c,p,1,0,0,"")){t.b("<input ");if(!t.s(t.f("autocomplete",c,p,1),c,p,1,0,0,"")){t.b("autocomplete=\"off\"");};t.b(" class=\"form-control\" ");if(!t.s(t.f("isEnabled",c,p,1),c,p,1,0,0,"")){t.b("readonly");};t.b(" ");if(t.s(t.f("maxLength",c,p,1),c,p,0,890,915,"{{ }}")){t.rs(c,p,function(c,p,t){t.b("maxlength=\"");t.b(t.v(t.f("maxLength",c,p,0)));t.b("\"");});c.pop();}if(t.s(t.f("min",c,p,1),c,p,0,937,951,"{{ }}")){t.rs(c,p,function(c,p,t){t.b(" min=\"");t.b(t.v(t.f("min",c,p,0)));t.b("\"");});c.pop();}if(t.s(t.f("max",c,p,1),c,p,0,967,981,"{{ }}")){t.rs(c,p,function(c,p,t){t.b(" max=\"");t.b(t.v(t.f("max",c,p,0)));t.b("\"");});c.pop();}t.b(" placeholder=\"");t.b(t.v(t.f("placeholder",c,p,0)));t.b("\" type=\"file\" name=\"");t.b(t.v(t.f("name",c,p,0)));t.b("\" id=\"");t.b(t.v(t.f("name",c,p,0)));t.b("\" value=\"");t.b(t.v(t.f("value",c,p,0)));t.b("\" />");t.b("\n" + i);t.b("    ");if(t.s(t.f("post",c,p,1),c,p,0,1096,1151,"{{ }}")){t.rs(c,p,function(c,p,t){t.b("<span class=\"input-group-addon\">");t.b(t.t(t.f("post",c,p,0)));t.b("</span></div>");});c.pop();}t.b("\n" + i);t.b("    ");if(!t.s(t.f("post",c,p,1),c,p,1,0,0,"")){if(t.s(t.f("pre",c,p,1),c,p,0,1182,1188,"{{ }}")){t.rs(c,p,function(c,p,t){t.b("</div>");});c.pop();}};t.b("\n" + i);t.b(t.rp("<berry__addons1",c,p,"		"));t.b("		");};if(t.s(t.f("success",c,p,1),c,p,0,1253,1348,"{{ }}")){t.rs(c,p,function(c,p,t){t.b("<div class=\"text-success\" style=\"padding-top: 7px;\">");t.b(t.v(t.f("value",c,p,0)));t.b(" <i class=\"fa fa-check\"></i></div>");});c.pop();}t.b("\n" + i);t.b("	</div>");t.b("\n" + i);t.b("</div>");return t.fl(); },partials: {"<berry__label0":{name:"berry__label", partials: {}, subs: {  }},"<berry__addons1":{name:"berry__addons", partials: {}, subs: {  }}}, subs: {  }});
  templates["berry_radio_collection"] = new Hogan.Template({code: function (c,p,i) { var t=this;t.b(i=i||"");t.b("<div class=\"row clearfix form-group ");t.b(t.v(t.f("modifiers",c,p,0)));t.b(" ");if(t.s(t.d("multiple.duplicate",c,p,1),c,p,0,73,136,"{{ }}")){t.rs(c,p,function(c,p,t){t.b("dupable\" data-min=\"");t.b(t.v(t.d("multiple.min",c,p,0)));t.b("\" data-max=\"");t.b(t.v(t.d("multiple.max",c,p,0)));});c.pop();}t.b("\" name=\"");t.b(t.v(t.f("name",c,p,0)));t.b("\" data-type=\"");t.b(t.v(t.f("type",c,p,0)));t.b("\">");t.b("\n" + i);t.b(t.rp("<berry__label0",c,p,"	"));if(t.s(t.d("multiple.duplicate",c,p,1),c,p,0,242,392,"{{ }}")){t.rs(c,p,function(c,p,t){t.b("	<div class=\"duplicate add btn btn-default\"><i class=\"fa fa-plus\"></i></div>");t.b("\n" + i);t.b("	<div class=\"btn btn-default remove\"><i class=\"fa fa-minus\"></i></div>");t.b("\n" + i);});c.pop();}if(t.s(t.f("label",c,p,1),c,p,0,427,522,"{{ }}")){t.rs(c,p,function(c,p,t){t.b("	");if(t.s(t.f("inline",c,p,1),c,p,0,440,463,"{{ }}")){t.rs(c,p,function(c,p,t){t.b("<div class=\"col-md-12\">");});c.pop();}t.b("\n" + i);t.b("	");if(!t.s(t.f("inline",c,p,1),c,p,1,0,0,"")){t.b("<div class=\"col-md-8\">");};t.b("\n" + i);});c.pop();}if(!t.s(t.f("label",c,p,1),c,p,1,0,0,"")){t.b("	<div class=\"col-md-12\">");t.b("\n" + i);};t.b("		");if(t.s(t.f("pre",c,p,1),c,p,0,592,665,"{{ }}")){t.rs(c,p,function(c,p,t){t.b("<div class=\"input-group\"><span class=\"input-group-addon\">");t.b(t.t(t.f("pre",c,p,0)));t.b("</span>");});c.pop();}t.b("\n" + i);t.b("		");if(!t.s(t.f("pre",c,p,1),c,p,1,0,0,"")){if(t.s(t.f("post",c,p,1),c,p,0,693,718,"{{ }}")){t.rs(c,p,function(c,p,t){t.b("<div class=\"input-group\">");});c.pop();}};t.b("\n");t.b("\n" + i);t.b("			<table class=\"table table-striped\" >");t.b("\n" + i);t.b("				<thead>");t.b("\n" + i);t.b("				<tr>");t.b("\n" + i);t.b("					<th>&nbsp;</th>");t.b("\n" + i);if(t.s(t.f("options",c,p,1),c,p,0,836,892,"{{ }}")){t.rs(c,p,function(c,p,t){t.b("					<th style=\"text-align:center\">");t.b(t.v(t.f("label",c,p,0)));t.b("</th>");t.b("\n" + i);});c.pop();}t.b("				</tr>");t.b("\n");t.b("\n" + i);t.b("				</thead>");t.b("\n" + i);t.b("				<tbody>");t.b("\n" + i);if(t.s(t.f("labels",c,p,1),c,p,0,956,1375,"{{ }}")){t.rs(c,p,function(c,p,t){t.b("				<tr>");t.b("\n" + i);t.b("					<td>");t.b("\n" + i);t.b("						<label ");if(t.s(t.f("inline",c,p,1),c,p,0,1000,1020,"{{ }}")){t.rs(c,p,function(c,p,t){t.b("class=\"radio-inline\"");});c.pop();}t.b(">");t.b("\n" + i);t.b("							");t.b(t.t(t.f("name",c,p,0)));if(!t.s(t.f("name",c,p,1),c,p,1,0,0,"")){t.b("&nbsp;");};t.b("\n" + i);t.b("						</label>");t.b("\n" + i);t.b("					</td>");t.b("\n");t.b("\n" + i);if(t.s(t.f("options",c,p,1),c,p,0,1119,1349,"{{ }}")){t.rs(c,p,function(c,p,t){t.b("					<td style=\"text-align:center\">");t.b("\n" + i);t.b("						<input data-label=\"");t.b(t.v(t.f("label",c,p,0)));t.b("\" name=\"");t.b(t.v(t.d("item.name",c,p,0)));t.b(t.v(t.f("name",c,p,0)));t.b("\" value=\"");t.b(t.v(t.f("value",c,p,0)));t.b("\" ");if(!t.s(t.f("isEnabled",c,p,1),c,p,1,0,0,"")){t.b("readonly");};t.b(" type=\"radio\" ");if(t.s(t.f("selected",c,p,1),c,p,0,1302,1317,"{{ }}")){t.rs(c,p,function(c,p,t){t.b("checked=checked");});c.pop();}t.b(" >");t.b("\n" + i);t.b("					</td>");t.b("\n" + i);});c.pop();}t.b("				</tr>");t.b("\n" + i);});c.pop();}t.b("		</tbody>");t.b("\n" + i);t.b("			</table>");t.b("\n");t.b("\n" + i);t.b("		");if(t.s(t.f("post",c,p,1),c,p,0,1422,1477,"{{ }}")){t.rs(c,p,function(c,p,t){t.b("<span class=\"input-group-addon\">");t.b(t.t(t.f("post",c,p,0)));t.b("</span></div>");});c.pop();}t.b("\n" + i);t.b("		");if(!t.s(t.f("post",c,p,1),c,p,1,0,0,"")){if(t.s(t.f("pre",c,p,1),c,p,0,1506,1512,"{{ }}")){t.rs(c,p,function(c,p,t){t.b("</div>");});c.pop();}};t.b("\n" + i);t.b(t.rp("<berry__addons1",c,p,"		"));t.b("	</div>");t.b("\n" + i);t.b("</div>");return t.fl(); },partials: {"<berry__label0":{name:"berry__label", partials: {}, subs: {  }},"<berry__addons1":{name:"berry__addons", partials: {}, subs: {  }}}, subs: {  }});
  templates["berry_scale"] = new Hogan.Template({code: function (c,p,i) { var t=this;t.b(i=i||"");t.b("<div class=\"row clearfix form-group ");t.b(t.v(t.f("modifiers",c,p,0)));t.b(" ");if(t.s(t.d("multiple.duplicate",c,p,1),c,p,0,73,136,"{{ }}")){t.rs(c,p,function(c,p,t){t.b("dupable\" data-min=\"");t.b(t.v(t.d("multiple.min",c,p,0)));t.b("\" data-max=\"");t.b(t.v(t.d("multiple.max",c,p,0)));});c.pop();}t.b("\" name=\"");t.b(t.v(t.f("name",c,p,0)));t.b("\" data-type=\"");t.b(t.v(t.f("type",c,p,0)));t.b("\">");t.b("\n" + i);t.b(t.rp("<berry__label0",c,p,"	"));if(t.s(t.d("multiple.duplicate",c,p,1),c,p,0,242,392,"{{ }}")){t.rs(c,p,function(c,p,t){t.b("	<div class=\"duplicate add btn btn-default\"><i class=\"fa fa-plus\"></i></div>");t.b("\n" + i);t.b("	<div class=\"btn btn-default remove\"><i class=\"fa fa-minus\"></i></div>");t.b("\n" + i);});c.pop();}if(t.s(t.f("label",c,p,1),c,p,0,427,522,"{{ }}")){t.rs(c,p,function(c,p,t){t.b("	");if(t.s(t.f("inline",c,p,1),c,p,0,440,463,"{{ }}")){t.rs(c,p,function(c,p,t){t.b("<div class=\"col-md-12\">");});c.pop();}t.b("\n" + i);t.b("	");if(!t.s(t.f("inline",c,p,1),c,p,1,0,0,"")){t.b("<div class=\"col-md-8\">");};t.b("\n" + i);});c.pop();}if(!t.s(t.f("label",c,p,1),c,p,1,0,0,"")){t.b("	<div class=\"col-md-12\">");t.b("\n" + i);};t.b("		");if(t.s(t.f("pre",c,p,1),c,p,0,592,665,"{{ }}")){t.rs(c,p,function(c,p,t){t.b("<div class=\"input-group\"><span class=\"input-group-addon\">");t.b(t.t(t.f("pre",c,p,0)));t.b("</span>");});c.pop();}t.b("\n" + i);t.b("		");if(!t.s(t.f("pre",c,p,1),c,p,1,0,0,"")){if(t.s(t.f("post",c,p,1),c,p,0,693,718,"{{ }}")){t.rs(c,p,function(c,p,t){t.b("<div class=\"input-group\">");});c.pop();}};t.b("\n");t.b("\n" + i);t.b("			<table class=\"table table-striped\">");t.b("\n" + i);t.b("				<thead>");t.b("\n" + i);t.b("				<tr>");t.b("\n" + i);t.b("					<th></th>");t.b("\n" + i);if(t.s(t.f("options",c,p,1),c,p,0,829,859,"{{ }}")){t.rs(c,p,function(c,p,t){t.b("					<th>");t.b(t.v(t.f("label",c,p,0)));t.b("</th>");t.b("\n" + i);});c.pop();}t.b("					<th></th>");t.b("\n" + i);t.b("				</tr>");t.b("\n");t.b("\n" + i);t.b("				</thead>");t.b("\n" + i);t.b("				<tbody>");t.b("\n" + i);t.b("				<tr>");t.b("\n" + i);t.b("					<td>");t.b("\n" + i);t.b("						");t.b(t.t(t.f("low",c,p,0)));t.b("\n" + i);t.b("					</td>");t.b("\n");t.b("\n" + i);if(t.s(t.f("options",c,p,1),c,p,0,987,1178,"{{ }}")){t.rs(c,p,function(c,p,t){t.b("					<td>");t.b("\n" + i);t.b("						<input data-label=\"");t.b(t.v(t.f("label",c,p,0)));t.b("\" name=\"");t.b(t.v(t.f("name",c,p,0)));t.b("\" value=\"");t.b(t.v(t.f("value",c,p,0)));t.b("\" ");if(!t.s(t.f("isEnabled",c,p,1),c,p,1,0,0,"")){t.b("readonly");};t.b(" type=\"radio\" ");if(t.s(t.f("selected",c,p,1),c,p,0,1131,1146,"{{ }}")){t.rs(c,p,function(c,p,t){t.b("checked=checked");});c.pop();}t.b(" >");t.b("\n" + i);t.b("					</td>");t.b("\n" + i);});c.pop();}t.b("					<td>");t.b("\n" + i);t.b("						");t.b(t.t(t.f("high",c,p,0)));t.b("\n" + i);t.b("					</td>");t.b("\n" + i);t.b("				</tr>");t.b("\n");t.b("\n" + i);t.b("		</tbody>");t.b("\n" + i);t.b("			</table>");t.b("\n");t.b("\n" + i);t.b("		");if(t.s(t.f("post",c,p,1),c,p,0,1275,1330,"{{ }}")){t.rs(c,p,function(c,p,t){t.b("<span class=\"input-group-addon\">");t.b(t.t(t.f("post",c,p,0)));t.b("</span></div>");});c.pop();}t.b("\n" + i);t.b("		");if(!t.s(t.f("post",c,p,1),c,p,1,0,0,"")){if(t.s(t.f("pre",c,p,1),c,p,0,1359,1365,"{{ }}")){t.rs(c,p,function(c,p,t){t.b("</div>");});c.pop();}};t.b("\n" + i);t.b(t.rp("<berry__addons1",c,p,"		"));t.b("	</div>");t.b("\n" + i);t.b("</div>");return t.fl(); },partials: {"<berry__label0":{name:"berry__label", partials: {}, subs: {  }},"<berry__addons1":{name:"berry__addons", partials: {}, subs: {  }}}, subs: {  }});
  templates["berry_tabs"] = new Hogan.Template({code: function (c,p,i) { var t=this;t.b(i=i||"");t.b("<ul class=\"nav nav-tabs\" style=\"margin-bottom:15px\">");t.b("\n" + i);if(t.s(t.f("sectionList",c,p,1),c,p,0,70,144,"{{ }}")){t.rs(c,p,function(c,p,t){t.b("	<li>");t.b("\n" + i);t.b("		<a href=\"#tab");t.b(t.v(t.f("index",c,p,0)));t.b("\" data-toggle=\"tab\">");t.b(t.t(t.f("text",c,p,0)));t.b("</a>");t.b("\n" + i);t.b("	</li>");t.b("\n" + i);});c.pop();}t.b("</ul>");return t.fl(); },partials: {}, subs: {  }});
  
  
  Berry.register({ type: 'radio_collection',
      acceptObject: true,
      create: function() {
          this.options = Berry.processOpts.call(this.owner, this.item, this).options;
          return Berry.render('berry_' + (this.elType || this.type), this);
      },
      setup: function() {
          this.$el = this.self.find('[type=radio]');
          this.$el.off();
          if(this.onchange !== undefined) {
              this.on('change', this.onchange);
          }
          this.$el.change($.proxy(function(){this.trigger('change');}, this));
      },
      getValue: function() {
          var values = {}
          for(var label in this.labels){
              var selected = this.self.find('[name="'+this.name+this.labels[label].name+'"][type="radio"]:checked').data('label');
              for(var i in this.item.options) {
                  if(this.item.options[i].label == selected) {
                      values[this.labels[label].name] = this.item.options[i].value;
                      // return this.item.options[i].value;
                  }
              }
          }
          return values;
      },
      setValue: function(value) {
          this.value = value;
          for(var i in this.labels){
              this.self.find('[name="'+this.name+this.labels[i].name+'"][value="' + this.value[this.labels[i].name] + '"]').prop('checked', true);
          }
      },
      // set: function(value){
      // 	if(this.value != value) {
      // 		//this.value = value;
      // 		this.setValue(value);
      // 		this.trigger('change');
      // 	}
      // },
      displayAs: function() {
          for(var i in this.item.options) {
              if(this.item.options[i].value == this.lastSaved) {
                  return this.item.options[i].label;
              }
          }
      },
      focus: function(){
          this.self.find('[name='+this.labels[0].name+'][type="radio"]:checked').focus();
      }
  });
  
  Berry.register({ type: 'scale',
      create: function() {
         // this.options = [];
          this.item.choices = [];
          this.options = Berry.processOpts.call(this.owner, this.item, this).options;
          return Berry.render('berry_' + (this.elType || this.type), this);
      },
      setup: function() {
          this.$el = this.self.find('[type=radio]');
          this.$el.off();
          if(this.onchange !== undefined) {
              this.on('change', this.onchange);
          }
          this.$el.change($.proxy(function(){this.trigger('change');}, this));
      },
      getValue: function() {
          var selected = this.self.find('[type="radio"]:checked').data('label');
          for(var i in this.item.options) {
              if(this.item.options[i].label == selected) {
                  return this.item.options[i].value;
              }
          }
      },
      setValue: function(value) {
          this.value = value;
          this.self.find('[value="' + this.value + '"]').prop('checked', true);
      },
      displayAs: function() {
          for(var i in this.item.options) {
              if(this.item.options[i].value == this.lastSaved) {
                  return this.item.options[i].label;
              }
          }
      },
      focus: function(){
          this.self.find('[type="radio"]:checked').focus();
      }
  });
  
  Berry.register({ type: 'check_collection',
      defaults: {container: 'span', acceptObject: true},
      create: function() {
          this.options = Berry.processOpts.call(this.owner, this.item, this).options;
  
          this.checkStatus(this.value);
          return Berry.render('berry_check_collection', this);
      },
      checkStatus: function(value){
          if(value === true || value === "true" || value === 1 || value === "1" || value === "on" || value == this.truestate){
              this.value = true;
          }else{
              this.value = false;
          }
      },
      setup: function() {
          this.$el = this.self.find('[type=checkbox]');
          this.$el.off();
          if(this.onchange !== undefined) {
              this.on('change', this.onchange);
          }
          this.$el.change($.proxy(function(){this.trigger('change');},this));
      },
      getValue: function() {
  
          var values = [];
          for(var opt in this.options){
              if(this.self.find('[name="'+this.options[opt].value+'"][type="checkbox"]').is(':checked')){
                  // values[this.options[opt].value] = (this.truestate || true);
                  values.push(this.options[opt].value);
              }else{
                  if(typeof this.falsestate !== 'undefined') {
                      // values[this.options[opt].value] = this.falsestate;
                  }else{
                      // values[this.options[opt].value] = false;
                  }
              }
              
          }
          return values;
      },
      setValue: function(value) {
          // this.checkStatus(value);
          // this.$el.prop('checked', this.value);
          // this.value = value;
          // debugger;
          this.value = value;
              this.self.find('[type="checkbox"]').prop('checked', false);
          for(var i in this.value){
              this.self.find('[name="'+this.value[i]+'"][type="checkbox"]').prop('checked', true);
          }
      },
      displayAs: function() {
          for(var i in this.item.options) {
              if(this.item.options[i].value == this.lastSaved) {
                  return this.item.options[i].name;
              }
          }
      },
      focus: function(){
          //this.$el.focus();
          this.self.find('[type=checkbox]:first').focus();
      },
      satisfied: function(){
          return this.$el.is(':checked');
      },
  });
  
      Berry.register({ type: 'time',
          defaults: { elType: 'text' },
          setup: function() {
              this.$el = this.self.find('input');
              this.$el.off();
              if(this.onchange !== undefined){ this.$el.on('input',this.onchange);}
              this.$el.on('input', $.proxy(function(){this.trigger('change');}, this));
  
              // this.$el.timepicker(this.item.timepicker || {});
  
        this.$el.datetimepicker($.extend({},{format: "hh:mm A"},this.item.timepicker));
  
              // this.$el.datetimepicker(this.item.timepicker || {format: "h:m A"});
  
          }
      });
  Berry.register({
      type: 'barcode',
      defaults: { elType: 'text' },
      update: function(item, silent) {
          if(typeof item === 'object') {
              $.extend(this.item, item);
          }
          $.extend(this, this.item);
          this.setValue(this.value);
          this.render();
          this.setup();
          if(!silent) {
              this.trigger('change');
          }
      },
      satisfied: function(){
          return (this.value.toLowerCase().trim() == this.item.help.toLowerCase().trim());
      }
  });
  // jQuery tagEditor v1.0.20
  // https://github.com/Pixabay/jQuery-tagEditor
  !function(t){t.fn.tagEditorInput=function(){var e=" ",i=t(this),a=parseInt(i.css("fontSize")),r=t("<span/>").css({position:"absolute",top:-9999,left:-9999,width:"auto",fontSize:i.css("fontSize"),fontFamily:i.css("fontFamily"),fontWeight:i.css("fontWeight"),letterSpacing:i.css("letterSpacing"),whiteSpace:"nowrap"}),l=function(){if(e!==(e=i.val())){r.text(e);var t=r.width()+a;20>t&&(t=20),t!=i.width()&&i.width(t)}};return r.insertAfter(i),i.bind("keyup keydown focus",l)},t.fn.tagEditor=function(e,a,r){function l(t){return t.replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&#39;")}var n,o=t.extend({},t.fn.tagEditor.defaults,e),c=this;if(o.dregex=new RegExp("["+o.delimiter.replace("-","-")+"]","g"),"string"==typeof e){var s=[];return c.each(function(){var i=t(this),l=i.data("options"),n=i.next(".tag-editor");if("getTags"==e)s.push({field:i[0],editor:n,tags:n.data("tags")});else if("addTag"==e){if(l.maxTags&&n.data("tags").length>=l.maxTags)return!1;t('<li><div class="tag-editor-spacer">&nbsp;'+l.delimiter[0]+'</div><div class="tag-editor-tag"></div><div class="tag-editor-delete"><i></i></div></li>').appendTo(n).find(".tag-editor-tag").html('<input type="text" maxlength="'+l.maxLength+'">').addClass("active").find("input").val(a).blur(),r?t(".placeholder",n).remove():n.click()}else"removeTag"==e?(t(".tag-editor-tag",n).filter(function(){return t(this).text()==a}).closest("li").find(".tag-editor-delete").click(),r||n.click()):"destroy"==e&&i.removeClass("tag-editor-hidden-src").removeData("options").off("focus.tag-editor").next(".tag-editor").remove()}),"getTags"==e?s:this}return window.getSelection&&t(document).off("keydown.tag-editor").on("keydown.tag-editor",function(e){if(8==e.which||46==e.which||e.ctrlKey&&88==e.which){try{var a=getSelection(),r="BODY"==document.activeElement.tagName?t(a.getRangeAt(0).startContainer.parentNode).closest(".tag-editor"):0}catch(e){r=0}if(a.rangeCount>0&&r&&r.length){var l=[],n=a.toString().split(r.prev().data("options").dregex);for(i=0;i<n.length;i++){var o=t.trim(n[i]);o&&l.push(o)}return t(".tag-editor-tag",r).each(function(){~t.inArray(t(this).text(),l)&&t(this).closest("li").find(".tag-editor-delete").click()}),!1}}}),c.each(function(){function e(){!o.placeholder||c.length||t(".deleted, .placeholder, input",s).length||s.append('<li class="placeholder"><div>'+o.placeholder+"</div></li>")}function i(i){var a=c.toString();c=t(".tag-editor-tag:not(.deleted)",s).map(function(e,i){var a=t.trim(t(this).hasClass("active")?t(this).find("input").val():t(i).text());return a?a:void 0}).get(),s.data("tags",c),r.val(c.join(o.delimiter[0])),i||a!=c.toString()&&o.onChange(r,s,c),e()}function a(e){for(var a,n=e.closest("li"),d=e.val().replace(/ +/," ").split(o.dregex),g=e.data("old_tag"),f=c.slice(0),h=!1,u=0;u<d.length;u++)if(v=t.trim(d[u]).slice(0,o.maxLength),o.forceLowercase&&(v=v.toLowerCase()),a=o.beforeTagSave(r,s,f,g,v),v=a||v,a!==!1&&v&&(o.removeDuplicates&&~t.inArray(v,f)&&t(".tag-editor-tag",s).each(function(){t(this).text()==v&&t(this).closest("li").remove()}),f.push(v),n.before('<li><div class="tag-editor-spacer">&nbsp;'+o.delimiter[0]+'</div><div class="tag-editor-tag">'+l(v)+'</div><div class="tag-editor-delete"><i></i></div></li>'),o.maxTags&&f.length>=o.maxTags)){h=!0;break}e.attr("maxlength",o.maxLength).removeData("old_tag").val(""),h?e.blur():e.focus(),i()}var r=t(this),c=[],s=t("<ul "+(o.clickDelete?'oncontextmenu="return false;" ':"")+'class="tag-editor"></ul>').insertAfter(r);r.addClass("tag-editor-hidden-src").data("options",o).on("focus.tag-editor",function(){s.click()}),s.append('<li style="width:1px">&nbsp;</li>');var d='<li><div class="tag-editor-spacer">&nbsp;'+o.delimiter[0]+'</div><div class="tag-editor-tag"></div><div class="tag-editor-delete"><i></i></div></li>';s.click(function(e,i){var a,r,l=99999;if(!window.getSelection||""==getSelection())return o.maxTags&&s.data("tags").length>=o.maxTags?(s.find("input").blur(),!1):(n=!0,t("input:focus",s).blur(),n?(n=!0,t(".placeholder",s).remove(),i&&i.length?r="before":t(".tag-editor-tag",s).each(function(){var n=t(this),o=n.offset(),c=o.left,s=o.top;e.pageY>=s&&e.pageY<=s+n.height()&&(e.pageX<c?(r="before",a=c-e.pageX):(r="after",a=e.pageX-c-n.width()),l>a&&(l=a,i=n))}),"before"==r?t(d).insertBefore(i.closest("li")).find(".tag-editor-tag").click():"after"==r?t(d).insertAfter(i.closest("li")).find(".tag-editor-tag").click():t(d).appendTo(s).find(".tag-editor-tag").click(),!1):!1)}),s.on("click",".tag-editor-delete",function(){if(t(this).prev().hasClass("active"))return t(this).closest("li").find("input").caret(-1),!1;var a=t(this).closest("li"),l=a.find(".tag-editor-tag");return o.beforeTagDelete(r,s,c,l.text())===!1?!1:(l.addClass("deleted").animate({width:0},o.animateDelete,function(){a.remove(),e()}),i(),!1)}),o.clickDelete&&s.on("mousedown",".tag-editor-tag",function(a){if(a.ctrlKey||a.which>1){var l=t(this).closest("li"),n=l.find(".tag-editor-tag");return o.beforeTagDelete(r,s,c,n.text())===!1?!1:(n.addClass("deleted").animate({width:0},o.animateDelete,function(){l.remove(),e()}),i(),!1)}}),s.on("click",".tag-editor-tag",function(e){if(o.clickDelete&&(e.ctrlKey||e.which>1))return!1;if(!t(this).hasClass("active")){var i=t(this).text(),a=Math.abs((t(this).offset().left-e.pageX)/t(this).width()),r=parseInt(i.length*a),n=t(this).html('<input type="text" maxlength="'+o.maxLength+'" value="'+l(i)+'">').addClass("active").find("input");if(n.data("old_tag",i).tagEditorInput().focus().caret(r),o.autocomplete){var c=t.extend({},o.autocomplete),d="select"in c?o.autocomplete.select:"";c.select=function(e,i){d&&d(e,i),setTimeout(function(){s.trigger("click",[t(".active",s).find("input").closest("li").next("li").find(".tag-editor-tag")])},20)},n.autocomplete(c)}}return!1}),s.on("blur","input",function(d){d.stopPropagation();var g=t(this),f=g.data("old_tag"),h=t.trim(g.val().replace(/ +/," ").replace(o.dregex,o.delimiter[0]));if(h){if(h.indexOf(o.delimiter[0])>=0)return void a(g);if(h!=f)if(o.forceLowercase&&(h=h.toLowerCase()),cb_val=o.beforeTagSave(r,s,c,f,h),h=cb_val||h,cb_val===!1){if(f)return g.val(f).focus(),n=!1,void i();try{g.closest("li").remove()}catch(d){}f&&i()}else o.removeDuplicates&&t(".tag-editor-tag:not(.active)",s).each(function(){t(this).text()==h&&t(this).closest("li").remove()})}else{if(f&&o.beforeTagDelete(r,s,c,f)===!1)return g.val(f).focus(),n=!1,void i();try{g.closest("li").remove()}catch(d){}f&&i()}g.parent().html(l(h)).removeClass("active"),h!=f&&i(),e()});var g;s.on("paste","input",function(){t(this).removeAttr("maxlength"),g=t(this),setTimeout(function(){a(g)},30)});var f;s.on("keypress","input",function(e){o.delimiter.indexOf(String.fromCharCode(e.which))>=0&&(f=t(this),setTimeout(function(){a(f)},20))}),s.on("keydown","input",function(e){var i=t(this);if((37==e.which||!o.autocomplete&&38==e.which)&&!i.caret()||8==e.which&&!i.val()){var a=i.closest("li").prev("li").find(".tag-editor-tag");return a.length?a.click().find("input").caret(-1):!i.val()||o.maxTags&&s.data("tags").length>=o.maxTags||t(d).insertBefore(i.closest("li")).find(".tag-editor-tag").click(),!1}if((39==e.which||!o.autocomplete&&40==e.which)&&i.caret()==i.val().length){var l=i.closest("li").next("li").find(".tag-editor-tag");return l.length?l.click().find("input").caret(0):i.val()&&s.click(),!1}if(9==e.which){if(e.shiftKey){var a=i.closest("li").prev("li").find(".tag-editor-tag");if(a.length)a.click().find("input").caret(0);else{if(!i.val()||o.maxTags&&s.data("tags").length>=o.maxTags)return r.attr("disabled","disabled"),void setTimeout(function(){r.removeAttr("disabled")},30);t(d).insertBefore(i.closest("li")).find(".tag-editor-tag").click()}return!1}var l=i.closest("li").next("li").find(".tag-editor-tag");if(l.length)l.click().find("input").caret(0);else{if(!i.val())return;s.click()}return!1}if(!(46!=e.which||t.trim(i.val())&&i.caret()!=i.val().length)){var l=i.closest("li").next("li").find(".tag-editor-tag");return l.length?l.click().find("input").caret(0):i.val()&&s.click(),!1}if(13==e.which)return s.trigger("click",[i.closest("li").next("li").find(".tag-editor-tag")]),o.maxTags&&s.data("tags").length>=o.maxTags&&s.find("input").blur(),!1;if(36!=e.which||i.caret()){if(35==e.which&&i.caret()==i.val().length)s.find(".tag-editor-tag").last().click();else if(27==e.which)return i.val(i.data("old_tag")?i.data("old_tag"):"").blur(),!1}else s.find(".tag-editor-tag").first().click()});for(var h=o.initialTags.length?o.initialTags:r.val().split(o.dregex),u=0;u<h.length&&!(o.maxTags&&u>=o.maxTags);u++){var v=t.trim(h[u].replace(/ +/," "));v&&(o.forceLowercase&&(v=v.toLowerCase()),c.push(v),s.append('<li><div class="tag-editor-spacer">&nbsp;'+o.delimiter[0]+'</div><div class="tag-editor-tag">'+l(v)+'</div><div class="tag-editor-delete"><i></i></div></li>'))}i(!0),o.sortable&&t.fn.sortable&&s.sortable({distance:5,cancel:".tag-editor-spacer, input",helper:"clone",update:function(){i()}})})},t.fn.tagEditor.defaults={initialTags:[],maxTags:0,maxLength:50,delimiter:",;",placeholder:"",forceLowercase:!0,removeDuplicates:!0,clickDelete:!1,animateDelete:175,sortable:!0,autocomplete:null,onChange:function(){},beforeTagSave:function(){},beforeTagDelete:function(){}}}(jQuery);