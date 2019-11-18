<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate, max-stale=0, post-check=0, pre-check=0" />
    <link rel="icon"  type="image/png" href="/assets/icons/fontawesome/gray/32/address-book.png">
    <title>Admin | BComply</title>
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <!--<link href="../../assets/css/ie10-viewport-bug-workaround.css" rel="stylesheet">-->
    <!-- Custom styles for this template -->
    <link href="/assets/css/BComply.css" rel="stylesheet">
    <link href="/assets/css/toastr.min.css" rel="stylesheet">
    <link href="/assets/css/font-awesome.min.css" rel="stylesheet">

    <!-- Just for debugging purposes. Don't actually copy these 2 lines! -->
    <!--[if lt IE 9]><script src="../../assets/js/ie8-responsive-file-warning.js"></script><![endif]-->
    <!--<script src="../../assets/js/ie-emulation-modes-warning.js"></script>-->
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <link data-name="vs/editor/editor.main" rel="stylesheet" href="/assets/js/vendor/vs/editor/editor.main.css">
  </head>
  <body>
    <nav class="navbar navbar-inverse navbar-fixed-top">
      <div class="container-fluid">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="/admin" style="background: #d9534f;padding: 12px 0px 0px 18px;">
            <h3 style="color:#fff;margin:0px;"><i class="fa fa-address-book fa-fw"></i> BComply</h3>
          </a>
            <ul class="nav navbar-nav  hidden-xs">
                <li><a href="#"><h4 style="margin:0">{{$title}}</h4></a></li>
            </ul>
          <ul class="nav navbar-nav navbar-right hidden-xs">
          </li>
            <li><a href="#"><h4 style="margin:0"></h4></a></li>
          </ul>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
          <ul class="nav navbar-nav navbar-right">
          <li><a href="/"><h4 style="margin:0;">BComply Admin</h4></a>
            <li class="dropdown">
              <a href="#" class="dropdown-toggle user-info" data-toggle="dropdown" role="button">
                <img class="gravatar" src="https://www.gravatar.com/avatar/{{ md5(Auth::user()->email) }}?d=mm" /> 
                {{ Auth::user()->first_name }} {{ Auth::user()->last_name }} 
                <span class="caret"></span>
              </a>
              <ul class="dropdown-menu">
                <li><a href="/"><i class="fa fa-arrow-left"></i> Home</a></li>
                <li><a href="{{ url('/logout') }}"><i class="fa fa-times-circle"></i> Logout</a></li>
              </ul>
            </li>
          </ul>
          <ul class="nav navbar-nav navbar-right visible-xs-block">
            @can('manage','App\User')
            <li><a href="/admin/users"><i class="fa fa-user fa-fw"></i>&nbsp; Users</a></li>
            @endcan
            @can('manage','App\Team')
            <li><a href="/admin/teams"><i class="fa fa-users fa-fw"></i>&nbsp; Teams</a></li>
            @endcan
            @can('manage','App\Scenario')
            <li><a href="/admin/scenarios"><i class="fa fa-notes-medical fa-fw"></i>&nbsp; Scenarios</a></li>
            @endcan
            @can('manage_product','App\Library')
            <li><a href="/admin/products" ><i class="fa fa-pills fa-fw"></i>&nbsp; Products</a></li>
            @endcan
            @can('manage_prescriber','App\Library')
            <li><a href="/admin/prescribers" ><i class="fa fa-user-md fa-fw"></i>&nbsp; Prescribers</a></li>
            @endcan
            @can('manage_solution','App\Library')
            <li><a href="/admin/solutions" ><i class="fa fa-user-md fa-fw"></i>&nbsp; Solutions</a></li>
            @endcan
            @can('manage_lab','App\Library')
            <li><a href="/admin/labs" ><i class="fa fa-flask fa-fw"></i>&nbsp; Labs</a></li>
            @endcan
          </ul>
        </div>
      </div>
    </nav>
    <div class="col-sm-3 col-md-2 sidebar">

      <ul class="nav nav-sidebar">
        @can('manage','App\User')
        <li class="@if($page=="users") active @endif"><a href="/admin/users"><i class="fa fa-user fa-fw"></i>&nbsp; Users</a></li>
        @endcan
        @can('manage','App\Team')
        <li class="@if($page=="teams" || $page=="members" || $page=="notes" || $page=="messages") active @endif"><a href="/admin/teams"><i class="fa fa-users fa-fw"></i>&nbsp; Teams</a></li>
        @endcan
        @can('manage','App\Scenario')
        <li class="@if($page=="scenarios") active @endif"><a href="/admin/scenarios"><i class="fa fa-notes-medical fa-fw"></i>&nbsp; Scenarios</a></li>
        @endcan
        @can('manage_product','App\Library')
        <li class="@if($page=="products") active @endif"><a href="/admin/products" ><i class="fa fa-pills fa-fw"></i>&nbsp; Products</a></li>
        @endcan
        @can('manage_prescriber','App\Library')
        <li class="@if($page=="prescribers") active @endif"><a href="/admin/prescribers" ><i class="fa fa-user-md fa-fw"></i>&nbsp; Prescribers</a></li>
        @endcan
        @can('manage_solution','App\Library')
        <li class="@if($page=="solutions") active @endif"><a href="/admin/solutions" ><i class="fa fa-user-md fa-fw"></i>&nbsp; Solutions</a></li>
        @endcan
        @can('manage_lab','App\Library')
        <li class="@if($page=="labs") active @endif"><a href="/admin/labs" ><i class="fa fa-flask fa-fw"></i>&nbsp; Labs</a></li>
        @endcan
      </ul>
    </div>
    <div class="container-fluid" id="main-container">
      <div class="row">
        <div class="col-sm-12 admin-main">
            <div id="content">
              <div id="dataGrid"></div>
            </div>
        </div>
      </div>
    </div>
    <script src='/assets/js/vendor/jquery.min.js'></script>
    <script src="/assets/js/vendor/bootstrap.min.js"></script>
    <script src="/assets/js/vendor/lodash.min.js"></script>
    <script>_.findWhere = _.find; _.where = _.filter;_.pluck = _.map;_.contains = _.includes;</script>
    <script src='/assets/js/vendor/hogan.min.js'></script>
    <script src='/assets/js/vendor/toastr.min.js'></script> 
    <script src='/assets/js/vendor/gform_bootstrap.min.js'></script> 
    <script src="/assets/js/admin/fields.js"></script>
    <script src="/assets/js/admin/ajax_handler.js"></script>
    <script src='/assets/js/vendor/berry.full.js'></script> 
    <script src='/assets/js/vendor/bootstrap.full.berry.js'></script> 
    <script src='/assets/js/vendor/berrytables.full.js'></script> 
    <script src="/assets/js/admin/libs.js"></script>
    <script src="/assets/js/admin/admin.js"></script>
<script>

this.app=ajax;
this.data = {};
page = "{{ $page }}";
if (page!=='') {
    id = {{ is_null($id)?"null":$id }};
    this.app.get(page,{id:id},function(data) {
    this.data[page] = data;
    build_table.call(this, "{{ $page }}", {container:'#dataGrid'});
    });
}
</script>
  </body>
</html>
