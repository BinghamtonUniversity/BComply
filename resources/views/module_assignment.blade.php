@extends('default.default')

@section('title', 'Youtube Module')

@section('content')
<div class="row">
    <div class="col-sm-12" style="text-align:center;">
        <center><h3 style="text-align:center;">{{$assignment->version->name}}</h3></center>
        @if($assignment->version->type === 'articulate_tincan')
            <iframe style="border:0px;" width="100%" height="570" src="{{url('/storage/modules/'.$assignment->module_id.'/versions/'.$assignment->module_version_id)}}/{{$assignment->version->reference->filename}}?activity_id={{$assignment->id}}&endpoint={{url('/api/tincan')}}&auth=0&actor=<?php echo htmlentities(json_encode(["name"=>Auth::user()->first_name.' '.Auth::user()->last_name,"mbox"=>Auth::user()->email]));?>"></iframe>
        @elseif($assignment->version->type === 'youtube')
            <iframe style="border:0px;" width="100%" height="570" src="https://www.youtube.com/embed/{{$assignment->version->reference->code}}?autoplay=0&showinfo=0&controls=0&?modestbranding=1&autohide=1&showinfo=" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
        @endif
    </div>
</div>
@endsection