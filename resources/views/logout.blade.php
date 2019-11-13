@extends('default.default')

@section('title', 'Logout')

@section('content')
<div class="row">
    <div class="col-sm-8 col-sm-offset-2">
    <center><h1 style="text-align:center;">BComply Logout</h1></center>
</div>
<div class="row">
    <div class="col-sm-8 col-sm-offset-2">
      <div class="panel panel-default">
        <div class="panel-body">
          You are now logged out of the BComply Application, but you may not be logged out of your Identity Provider.
          As a result, you may be able to log back into the BComply application from this browser without 
          re-entering your credentials.  If you <i>must</i> terminate your session immediately, 
          you will need to delete your browser's session cookies.  
          <br><br>
          <a href="/"><i class="fa fa-arrow-left fa-fw"></i> Return to Home</a>
        </div>
      </div>
    </div>
</div>
@endsection