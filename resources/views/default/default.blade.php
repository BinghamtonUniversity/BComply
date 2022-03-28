<?php if (!isset($page)) {$page = null;} ?>
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
  <title>@yield('title') BComply</title>
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
    <nav class="navbar navbar-default navbar-inverse navbar-fixed-top" style="background-color:#333;border-width:0px">
      <div class="container-fluid">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="/" style="background: #004333;color:white;padding: 0px 0px 0px 25px;">
            <h3 style="color:#fff;margin-top:12px;"><i class="fa fa-address-book"></i> BComply</h3>
          </a>
        </div>
        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
          <ul class="nav navbar-nav navbar-right">
            <li class="dropdown">
              <a href="#" style="color:#ccc;" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                @if(!is_null(Auth::user()))
                  <img class="gravatar" src="https://www.gravatar.com/avatar/{{ md5(Auth::user()->email) }}?d=mm" />
                  {{Auth::user()->first_name}} {{Auth::user()->last_name}} <span class="caret"></span>
                @else Welcome! @endif
              </a>
              @if(!is_null(Auth::user()))
                <ul class="dropdown-menu">
                  @can('view_admin_dashboard','App\User')<li  style="color:#ccc;" ><a href="/admin"><i class="fa fa-user-shield fa-fw"></i> Admin</a></li>@endcan
                  <li  style="color:#ccc;" ><a href="/logout"><i class="fa fa-times-circle fa-fw"></i> Logout</a></li>
                </ul>
              @endif
            </li>
            <li class="visible-xs-block">&nbsp;</li>
            <li class="visible-xs-block @if($page =="my_assignments") active @endif"><a href="/"><i class="fa fa-user fa-fw"></i>&nbsp; My Assignments</a></li>
            <li class="visible-xs-block @if($page =="my_workshops") active @endif"><a href="/workshops"><i class="fa fa-user fa-fw"></i>&nbsp; My Workshops</a></li>
            <li class="visible-xs-block @if($page  =="history") active @endif"><a href="/history"><i class="fa fa-history fa-fw"></i>&nbsp; My History</a></li>
            <li class="visible-xs-block @if($page =="shop") active @endif"><a href="/shop"><i class="fa fa-shopping-cart fa-fw"></i>&nbsp; Shop Courses</a></li>
            <li class="visible-xs-block @if($page =="calendar") active @endif"><a href="/calendar"><i class="fa fa-calendar fa-fw"></i>&nbsp; Google Calendar</a></li>
          </ul>
        </div><!-- /.navbar-collapse -->
      </div><!-- /.container-fluid -->
    </nav>

    @if(!is_null(Auth::user()))
      <div class="sidebar col-sm-3 col-md-2 ">
        <ul class="nav nav-sidebar">
          <li class="@if($page =="my_assignments") active @endif"><a href="/"><i class="fa fa-user fa-fw"></i>&nbsp; My Assignments</a></li>
          <li class="@if($page =="my_workshops") active @endif"><a href="/workshops"><i class="fa fa-user fa-fw"></i>&nbsp; My Workshops</a></li>
          <li class="@if($page  =="history") active @endif"><a href="/history"><i class="fa fa-history fa-fw"></i>&nbsp; My History</a></li>
          <li class="@if($page =="shop") active @endif"><a href="/shop"><i class="fa fa-shopping-cart fa-fw"></i>&nbsp; Shop Courses</a></li>
          <li class="@if($page =="calendar") active @endif"><a href="/calendar"><i class="fa fa-calendar fa-fw"></i>&nbsp; Google Calendar</a></li>
        </ul>
      </div>
    @endif


    <div class="container-fluid" id="main-container">
      <div class="row">
        <div class="col-sm-12 admin-main">
          <div id="content">
            @if(isset($help))
              <div class="alert alert-info">{{$help}}</div>
            @endif
            @yield('content')
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
    <script src='/assets/js/vendor/gform_bootstrap.js'></script>
    <script src='/assets/js/vendor/GrapheneDataGrid.min.js'></script>
    <script src='/assets/js/vendor/moment.js'></script>
    <script src='/assets/js/vendor/bootstrap-datetimepicker.min.js'></script>
    <script src="/assets/js/admin/admin.js"></script>
    <script>
      @if(isset($ids)) window.ids={!!json_encode($ids)!!}; @endif
      if (typeof window.ids !== 'undefined' && Array.isArray(window.ids)) {
        window.id = window.ids[window.ids.length-1]
      }
      @if(isset($actions)) window.actions={!!json_encode($actions)!!}; @endif
    </script>
    <script src="/assets/js/user/user_dashboard.js"></script>
    @if($page ==='shop' || $page==='modules_assignments')
        <script src="/assets/js/user/user_dashboard_{{$page}}.js"></script>
    @endif
</body>
</html>