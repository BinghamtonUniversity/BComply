@extends('default.default')

@section('title', 'Assignment History')

@section('content')
    <div class="row">
        <div class="col-sm-12" style="text-align:center;">
            <center><h1 style="text-align:center;">Your Previous Assignments</h1></center>
            <div class="row">
                <div class="col-sm-6 col-sm-offset-3">
                    @if(count($assignments) > 0)
{{--                        <center><h3 style="text-align:center;">Please Select a Module</h1></center>--}}
                        <div class="list-group">
                            @foreach ($assignments as $assignment)
                                @if(!is_null($assignment->date_completed))
                                    @if ($assignment->version->type === 'tincan')
                                        <ul class="list-group">
                                            @if(!is_null($assignment->date_completed))
                                                <li class="list-group-item">    <div class="badge pull-right">{{$assignment->status}} ({{$assignment->score * 100}}%)</div>
                                            @elseif(!is_null($assignment->date_started))
                                                <div class="badge pull-right">{{$assignment->status}}</div>
                                            @endif
                                                    {{$assignment->version->name}}</li>
                                        </ul>
                                    @endif
                                @endif
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <h4 style="margin-top:0px;">You do not have any assignments in your history!</h4>
                            <div>Contact someone if you feel that this is in error.</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection