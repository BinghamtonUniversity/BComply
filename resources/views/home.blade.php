@extends('default.default')

@section('title', 'Home')

@section('content')
<div class="row">
    <div class="col-sm-12" style="text-align:center;">
        <center><h1 style="text-align:center;">Welcome to BComply!</h1></center>
        <div class="row">
            <div class="col-sm-6 col-sm-offset-3">
            @if(count($modules) > 0)
                <center><h3 style="text-align:center;">Please Select a Module</h1></center>
                <div class="list-group">
                @foreach ($modules as $module)
                    <a class="list-group-item" 
                       href="/modules/{{$module->module_id}}/versions/{{$module->module_version_id}}/{{$module->filename}}?endpoint={{url('/api/tincan')}}&auth=0&actor=<?php echo htmlentities(json_encode(["name"=>Auth::user()->first_name.' '.Auth::user()->last_name,"mbox"=>Auth::user()->email]));?>">
                        {{$module->name}}
                    </a>
                @endforeach
                </div>
            @else
                <div class="alert alert-warning">
                    <h4 style="margin-top:0px;">You do not have any modules!</h4>
                    <div>Contact someone if you feel that this is in error.</div>
                </div>
            @endif
            </div>
        </div>
    </div>
</div>
@endsection