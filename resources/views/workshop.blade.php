<div class="col-xs-12 col-sm-6 col-md-4 col-lg-3">
    {{-- <a class="trigger-help"    href="/assignment/{{$assignment->id}}" --}}
        <a class="trigger-help"    href=""
            data-toggle="popover" data-placement="top" title="Description" data-content="{{$workshop->description}}"
           style="height: 350px;">
                <div class="panel panel-default" >
                    <div class="panel-header">
                        <div class="pull-right bg-primary" style="padding: 0 5px;">{{Str::snakeToTitle($workshop->public)}}</div>
                    </div>
                <div class="panel-body">
                    <div class="panel-body" style="padding-top:30px;text-align:center;">
                        <i class="fa fa-{{$workshop->icon}} fa-10x"></i>
                    </div>
                    <hr style="margin:0 0px;">
                    <div class="panel-body" style="height: 110px;overflow:scroll">
                            <div class="module-name">{{$workshop->name}}</div>
                    </div>
                </div>
                {{-- <div class="panel-footer">
                    @if(isset($assignment->date_due))
                        <div class="badge">Due: {{$assignment->date_due->format('m/d/y')}}</div>
                    @else
                        <div class="badge">No Due Date</div>
                    @endif
                </div> --}}
            </div>
        </a>

</div>