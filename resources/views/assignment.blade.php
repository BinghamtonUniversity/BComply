<div class="col-xs-12 col-sm-6 col-md-4 col-lg-3">
    @if((\Carbon\Carbon::now()>$assignment->date_due && !$assignment->module->past_due))
        <div class="panel panel-default past-due">
            <a class="trigger-help" href="#" data-toggle="popover" data-placement="bottom" title="Notice!" data-content=
                "This assignment is past due, and cannot be completed after the due date!">
                <div class="pull-right bg-primary" style="padding: 0 5px;">{{Str::snakeToTitle($assignment->status)}}</div>
                <div class="panel-body" style="padding-top:30px;text-align:center;">
                    <i class="fa fa-{{$assignment->module->icon}} fa-10x"></i>
                </div>
                <hr style="margin:0 0px;">
                <div class="panel-body">
                        <div class="module-name">{{$assignment->version->name}}</div>
                        <div class="badge">Overdue: {{$assignment->date_due->format('m/d/y')}}</div>
                </div>
            </a>
        </div>
    @else
        <div class="panel panel-default">
            <a class="trigger-help" href="/assignment/{{$assignment->id}}"
                data-toggle="popover" data-placement="bottom" title="Description" data-content="{{$assignment->module->description}}">
                <div class="pull-right bg-primary" style="padding: 0 5px;">{{Str::snakeToTitle($assignment->status)}}</div>
                <div class="panel-body" style="padding-top:30px;text-align:center;">
                    <i class="fa fa-{{$assignment->module->icon}} fa-10x"></i>
                </div>
                <hr style="margin:0 0px;">
                <div class="panel-body">
                        <div class="module-name">{{$assignment->version->name}}</div>
                        @if(isset($assignment->date_due))
                            <div class="badge">Due: {{$assignment->date_due->format('m/d/y')}}</div>
                        @else 
                            <div class="badge">No Due Date</div>
                        @endif
                </div>
            </a>
        </div>
    @endif
</div>