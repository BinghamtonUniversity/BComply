@extends('default.default')
@section('title', 'Assignment History')
@section('content')
    <div class="row">
        <div class="col-sm-12" style="text-align:center;">
            <center><h1 style="text-align:center;">Your Previous Assignments</h1></center>
            <div class="row">
                <div class="col-sm-6 col-sm-offset-3">
                    @if(count($assignments) > 0)
                        <div class="list-group">
                            @foreach ($assignments as $assignment)
{{--                                    @if ($assignment->version->type === 'articulate_tincan')--}}
                                        <ul class="list-group">
                                                <li class="list-group-item">
                                                    <div class="row">
                                                        <div class="col-lg-10 col-sm-10">
                                                            @if(!is_null($assignment->date_completed))
                                                                <div class="monaco-count-badge pull-left">Completed: {{$assignment->date_completed}}</div>
                                                                <div class="badge pull-right">Score: {{$assignment->score * 100}}%</div>
                                                            @else
                                                                <div class="monaco-count-badge pull-left">Expired</div>
                                                            @endif
{{--                                    @endif--}}
                                                                <b>{{$assignment->version->name}}</b>
                                                        </div>
                                                        @if(($assignment->status==='completed')||($assignment->status==='passed'))
                                                            <div class="col-lg-2 col-sm-2" id="certificate"><a href="/assignment/{{$assignment->id}}/certificate">Certificate</a></div>
                                                        @endif
                                                    </div>
                                                </li>
                                        </ul>
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