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
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><p>My Module History</p></li>
                    </ol>
                            @foreach ($assignments as $assignment)
                            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3">
{{--                                <div >--}}
                                    <a class="trigger-help" href="/assignment/{{$assignment->id}}"
                                        data-toggle="popover" data-placement="top" title="Description" data-content="{{$assignment->module->description}}"
                                    style="height: 350px">
                                        <div class="panel panel-default">
                                            <div class="panel-header">
                                                <div class="pull-right bg-primary" style="padding: 0 5px;">{{Str::snakeToTitle($assignment->status)}}</div>
                                            </div>
                                            <div class="panel-body">
                                                <div class="panel-body" style="padding-top:30px;text-align:center;">
                                                    <i class="fa fa-{{$assignment->module->icon}} fa-10x"></i>
                                                </div>

                                                <hr style="margin:0 0px;">

                                                <div class="panel-body" style="height: 110px;overflow: scroll;">
                                                        <div class="module-name">{{$assignment->version->name}}</div>
                                                </div>
                                            </div>
                                            <div class="panel-footer">
                                                <div class="badge">Completed: {{$assignment->date_completed->format('m/d/y')}}</div>
                                            <!-- @if(!is_null($assignment->score))<div class="">Score: {{$assignment->score}}</div>@endif -->
                                                @if(($assignment->status==='completed')||($assignment->status==='passed')||($assignment->status==='attended'))
                                                    <div class="" id="certificate"><a href="/assignment/{{$assignment->id}}/certificate"><i class="fa fa-download"></i> Download Certificate</a></div>
                                                @endif
                                            </div>
                                        </div>
                                    </a>
{{--                                </div>--}}
                            </div>
                            @endforeach
                            
                    @elseif(count($attendances) > 0)     
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><p>My Workshop History</p></li>
                    </ol>
                    @foreach ($attendances as $attendance)
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3">
                         
                            @if($attendance->workshop->public)
                                <a class="trigger-help" href="workshops/{{$attendance->workshop->id}}/offerings/{{$attendance->workshop_offering->id}}"
                                data-toggle="popover" data-placement="top" title="" data-content="Public"
                            style="height: 350px">
                            @else
                                <a class="trigger-help" href="workshops/{{$attendance->workshop->id}}/offerings/{{$attendance->workshop_offering->id}}"
                                data-toggle="popover" data-placement="top" title="" data-content="Private"
                            style="height: 350px">
                            @endif
                                <div class="panel panel-default">
                                    <div class="panel-header">
                                        <div class="pull-right bg-primary" style="padding: 0 5px;">{{Str::snakeToTitle($attendance->attendance)}}</div>
                                        @if($attendance->status != 'not applicable')
                                            <div class="pull-left bg-primary" style="padding: 0 5px;">{{Str::snakeToTitle($attendance->status)}}</div>
                                     
                                        @endif
                                       
                                    </div>
                                    <div class="panel-body">
                                        <div class="panel-body" style="padding-top:30px;text-align:center;">
                                            <i class="fa fa-{{$attendance->workshop->icon}} fa-10x"></i>
                                        </div>

                                        <hr style="margin:0 0px;">

                                        <div class="panel-body" style="height: 110px;overflow: scroll;">
                                                <div class="module-name">{{$attendance->workshop->name}}</div>
                                                <div class="workshop-description">{{$attendance->workshop->description}}</div>
                                        </div>
                                    </div>
                                    <div class="panel-footer">
                                            <div class="badge">Location: {{$attendance->workshop_offering->locations}}</div>
                                            <div class="badge">Type: {{$attendance->workshop_offering->type}}</div>
                                            <div class="badge">Instructor: {{$attendance->workshop_offering->instructor->first_name}} {{$attendance->workshop_offering->instructor->last_name}}</div>
                                            @if($attendance->workshop_offering->is_multi_day)
                                            @foreach($attendance->workshop_offering->multi_days as $day)
                                                <div class="badge">Date: {{$day}}</div>
                                            @endforeach  
                                        @else
                                            <div class="badge">Date: {{$attendance->workshop_offering->workshop_date}}</div></li>
                                        @endif
                                        {{-- <div class="badge">Location: {{$attendance->date_completed->format('m/d/y')}}</div> --}}
                                    {{-- <!-- @if(!is_null($assignment->score))<div class="">Score: {{$assignment->score}}</div>@endif --> --}}
                                        {{-- @if(($assignment->status==='completed')||($assignment->status==='passed')||($assignment->status==='attended'))
                                            <div class="" id="certificate"><a href="/assignment/{{$assignment->id}}/certificate"><i class="fa fa-download"></i> Download Certificate</a></div>
                                        @endif --}}
                                    </div>
                                </div>
                            </a>

                    </div>
                    @endforeach
                    @else
                 
                    <div class="col-sm-12">
                        <div class="alert alert-warning" style="text-align:center;align-content:center;margin:auto">
                            <h4 style="margin-top:0px;">You do not have any assignments in your history!</h4>
                            <div>Contact <a href="mailto:comply@binghamton.edu">comply@binghamton.edu</a> if you feel that this is an error.</div>
                        </div>
                    </div>
                    @endif
            </div>
        </div>
    </div>
@endsection