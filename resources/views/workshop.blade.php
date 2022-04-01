<div class="col-xs-12 col-sm-6 col-md-4 col-lg-3">
    {{-- <a class="trigger-help"    href="/assignment/{{$assignment->id}}" --}}
        @if($attendance->workshop->public)
        <a class="trigger-help" href="#"
        data-toggle="popover" data-placement="top" title="" data-content="Public"
    style="height: 350px">
    @else
        <a class="trigger-help" href="#"
        data-toggle="popover" data-placement="top" title="" data-content="Private"
    style="height: 350px">
    @endif
                <div class="panel panel-default" >
                    <div class="panel-header">
                        <div class="pull-right bg-primary" style="padding: 0 5px;">{{Str::snakeToTitle($attendance->status)}}</div>
                    </div>
                <div class="panel-body">
                    <div class="panel-body" style="padding-top:30px;text-align:center;">
                        <i class="fa fa-{{$attendance->workshop->icon}} fa-10x"></i>
                    </div>
                    <hr style="margin:0 0px;">
                    <div class="panel-body" style="height: 110px;overflow:scroll">
                            <div class="module-name">{{$attendance->workshop->name}}</div>
                            <div class="workshop-description">{{$attendance->workshop->description}}</div>
                    </div>
                </div>
                <div class="panel-footer">
                    <ul>
                        <li><div class="badge">Location: {{$attendance->workshop_offering->locations}}</div></li>
                        <li><div class="badge">Type: {{$attendance->workshop_offering->type}}</div></li>
                        <li><div class="badge">Instructor: {{$attendance->workshop_offering->instructor->first_name}} {{$attendance->workshop_offering->instructor->last_name}}</div></li>
                        <li><div class="badge">Date: {{$attendance->workshop_offering->workshop_date}}</div></li>
                    </ul>
                
                </div>
            </div>
        </a>

</div>