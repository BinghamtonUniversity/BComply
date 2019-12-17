@extends('default.default')

@section('title', 'Home')

@section('content')
<div class="row">
    <div class="col-sm-12" style="text-align:center;">
        <center><h1 style="text-align:center;">Welcome to BComply!</h1></center>
        <div class="row">
            <div class="col-sm-6 col-sm-offset-3">
            @if(count($assignments)> 0)
                <center><h3 style="text-align:center;">Please Select a Course To Begin</h1></center>
                <div class="list-group">
                @foreach ($assignments as $assignment)
                        @if(is_null($assignment->date_completed))
                            @if ($assignment->version->type === 'articulate_tincan')
                                <a class="list-group-item" href="/assignment/{{$assignment->id}}">
                                    @if(!is_null($assignment->date_completed))
                                        <div class="badge pull-right">{{$assignment->status}} ({{$assignment->score * 100}}%)</div>
                                    @elseif(!is_null($assignment->date_started))
                                        <div class="badge pull-right">{{$assignment->status}}</div>
                                    @endif
                                    {{$assignment->version->name}}
                                </a>
                            @elseif ($assignment->version->type === 'youtube')
                                <a class="list-group-item" href="/assignment/{{$assignment->id}}">
                                    @if(!is_null($assignment->date_completed))
                                        <div class="badge pull-right">{{$assignment->status}} ({{$assignment->score * 100}}%)</div>
                                    @elseif(!is_null($assignment->date_started))
                                        <div class="badge pull-right">{{$assignment->status}}</div>
                                    @endif
                                    {{$assignment->version->name}}
                                </a>
                            @endif
                        @endif
                        @endforeach
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <h4 style="margin-top:0px;">You do not have any assigned modules!</h4>
                            <div>Contact someone if you feel that this is in error.</div>
                        </div>
                    @endif

            </div>
        </div>
    </div>
</div>
@endsection