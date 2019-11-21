@extends('default.default')

@section('title', 'Youtube Module')

@section('content')
<div class="row">
    <div class="col-sm-12" style="text-align:center;">
        <center><h1 style="text-align:center;">Welcome to BComply!</h1></center>
        @if($assignment->version->type === 'tincan')
            <iframe width="1014" height="570" src="/modules/{{$assignment->module_id}}/versions/{{$assignment->module_version_id}}/{{$assignment->version->reference->filename}}?activity_id={{$assignment->id}}&endpoint={{url('/api/tincan')}}&auth=0&actor=<?php echo htmlentities(json_encode(["name"=>Auth::user()->first_name.' '.Auth::user()->last_name,"mbox"=>Auth::user()->email]));?>"></iframe>
        @elseif($assignment->version->type === 'youtube')
            <iframe width="1014" height="570" src="https://www.youtube.com/embed/{{$assignment->version->reference->code}}" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
        @endif
    </div>
</div>
@endsection