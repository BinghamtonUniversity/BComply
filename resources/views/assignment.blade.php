<div class="col-xs-12 col-sm-6 col-md-4 col-lg-3">
    @if(is_null($assignment->version))
        <div class="panel panel-default past-due" style="height: 350px;">
            <a class="trigger-help" href="#" data-toggle="popover" data-placement="top" title="ERROR!" data-content=
                "This assignment is corrupt.  Most likely, the module version associated with this assignment has been deleted!">
                <div class="panel panel-default past-due">
                    <div class="panel-body">
                        <div class="panel-body" style="padding-top:30px;text-align:center;">
                            <i class="fa fa-{{$assignment->module->icon}} fa-10x"></i>
                        </div>
                        <hr style="margin:0 0px;">
                        <div class="panel-body" style="height: 110px;overflow:scroll">
                            <div class="module-name">{{$assignment->module->name}}</div>
                        </div>
                    </div>
                    <div class="panel-footer">
                        <div class="badge"><i class="fa fa-exclamation-triangle"></i> Corrupt Assignment</div>
                    </div>
                </div>
            </a>
        </div>
    @elseif((\Carbon\Carbon::now()>$assignment->date_due && !$assignment->module->past_due))
        <div class="panel panel-default past-due" style="height: 350px;">
            <a class="trigger-help" href="#" data-toggle="popover" data-placement="top" title="Notice!" data-content=
                "This assignment is past due, and cannot be completed after the due date!"
               style="height: 350px;">
                <div class="pull-right bg-primary" style="padding: 0 5px;">{{Str::snakeToTitle($assignment->status)}}</div>
                <div class="panel panel-default past-due" style="border: none">
                    <div class="panel-body">
                        <div class="panel-body" style="padding-top:30px;text-align:center;">
                            <i class="fa fa-{{$assignment->module->icon}} fa-10x"></i>
                        </div>
                        <hr style="margin:0 0px;">
                        <div class="panel-body" style="height: 110px;overflow:scroll">
                                <div class="module-name">{{$assignment->version->name}}</div>
                        </div>
                    </div>
                    <div class="panel-footer" style="background-color: rgb(239 239 239);">
                        <div class="badge" style="color: whitesmoke;background-color: #777">Overdue: {{$assignment->date_due->format('m/d/y')}}</div>
                    </div>
                </div>
            </a>
        </div>
    @else

        <a class="trigger-help" href="/assignment/{{$assignment->id}}"
            data-toggle="popover" data-placement="top" title="Description" data-content="{{$assignment->module->description}}"
           style="height: 350px;">
                <div class="panel panel-default" >
                    <div class="panel-header">
                        <div class="pull-right bg-primary" style="padding: 0 5px;">{{Str::snakeToTitle($assignment->status)}}</div>
                    </div>
                <div class="panel-body">
                    <div class="panel-body" style="padding-top:30px;text-align:center;">
                        <i class="fa fa-{{$assignment->module->icon}} fa-10x"></i>
                    </div>
                    <hr style="margin:0 0px;">
                    <div class="panel-body" style="height: 110px;overflow:scroll">
                            <div class="module-name">{{$assignment->version->name}}</div>
                    </div>
                </div>
                {{-- 
                <div class="panel-footer">
                    @if(isset($assignment->date_due))
                        <div class="badge">Due: {{$assignment->date_due->format('m/d/y')}}</div>
                    @else
                        <div class="badge">No Due Date</div>
                    @endif
                </div>
                --}}
            </div>
        </a>

    @endif
</div>