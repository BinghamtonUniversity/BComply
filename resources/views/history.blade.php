@extends('default.default')
@section('title', 'Assignment History')
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">My History</a></li>
                </ol>
            </nav>
            <div class="row">
                    @if(count($assignments) > 0)
                            @foreach ($assignments as $assignment)
                            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3">
                            <div class="panel panel-default">
                                <a class="trigger-help" href="/assignment/{{$assignment->id}}"
                                    data-toggle="popover" data-placement="top" title="Description" data-content="{{$assignment->module->description}}">
                                    <div class="pull-right bg-primary" style="padding: 0 5px;">{{Str::snakeToTitle($assignment->status)}}</div>
                                    <div class="panel-body" style="padding-top:30px;text-align:center;">
                                        <i class="fa fa-{{$assignment->module->icon}} fa-10x"></i>
                                    </div>
                                    <hr style="margin:0 0px;">
                                    <div class="panel-body">
                                            <div class="module-name">{{$assignment->version->name}}</div>
                                            <div class="badge">Completed: {{$assignment->date_completed->format('m/d/y')}}</div>
                                            <!-- @if(!is_null($assignment->score))<div class="">Score: {{$assignment->score}}</div>@endif -->
                                            @if(($assignment->status==='completed')||($assignment->status==='passed')||($assignment->status==='attended'))
                                                <div class="" id="certificate"><a href="/assignment/{{$assignment->id}}/certificate"><i class="fa fa-download"></i> Download Certificate</a></div>
                                            @endif
                                    </div>
                                </a>
                            </div>
                            </div>
                            @endforeach
                    @else
                    <div class="col-sm-12">
                        <div class="alert alert-warning" style="text-align:center;align-content:center;margin:auto">
                            <h4 style="margin-top:0px;">You do not have any assignments in your history!</h4>
                            <div>Contact <a href="mailto:comply@binghamton.edu">comply@binghamton.edu if you feel that this is an error.</div>
                        </div>
                    </div>
                    @endif
            </div>
        </div>
    </div>
@endsection