<div class="col-xs-12 col-sm-6 col-md-4 col-lg-3">
    {{-- <a class="trigger-help"    href="/assignment/{{$assignment->id}}" --}}
        {{-- .on("click",function(grid_event) {     
            window.location = '/admin/workshops/'+ grid_event.model.attributes.workshop_id +'/offerings/'+grid_event.model.attributes.id+'/attendances';
          
    
        }) --}}
        @if($attendance->workshop->public)
        <a class="trigger-help" href="workshops/{{$attendance->workshop->id}}/offerings/{{$attendance->workshop_offering->id}}"
        data-toggle="popover" data-placement="top" title="" data-content="Public"
    style="height: 350px">
    @else
        <a class="trigger-help" href="workshops/{{$attendance->workshop->id}}/offerings/{{$attendance->workshop_offering->id}}"
        data-toggle="popover" data-placement="top" title="" data-content="Private"
    style="height: 350px">
    @endif
                <div class="panel panel-default" >
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
                        @if($attendance->workshop_offering->is_multi_day)
                            @foreach($attendance->workshop_offering->multi_days as $day)
                                <li><div class="badge">Date: {{$day}}</div></li>
                            @endforeach  
                        @else
                            <li><div class="badge">Date: {{$attendance->workshop_offering->workshop_date}}</div></li>
                        @endif
                      
                        
                    </ul>
                    @if(is_array($attendance->workshop->files) && count($attendance->workshop->files)!=0)
                    <p>Files</p>
                    <ul>
                        <!-- todo create download link --> 
                        @foreach($attendance->workshop->files as $file)
                        <li><a href=""> {{$file}}</div></li>
                            @endforeach  
                    </ul>
                
                    @endif
                  
                </div>
            </div>
        </a>

</div>