@extends('default.default')

@section('title', 'Training Module')

@section('content')
<div class="row">
    <div class="col-sm-12">
    @if(is_null($assignment))
        <div class="alert alert-danger" style="text-align:center;align-content:center;margin:auto">
            <div><h3>This assignment URL is not intended for you.</h3>To view your assignments, please visit: <a href="{{url('/')}}">{{url('/')}}</a></div>
        </div>
    @else
        @if((\Carbon\Carbon::now()>$assignment->date_due && !$assignment->module->past_due))
            <div class="alert alert-warning" style="text-align:center;align-content:center;margin:auto">
                <h4 style="margin-top:0px;">You can no longer view this module</h4>
                <div>This module is configured such that you can no longer view it once the due date has elapsed.</div>
            </div>
        @else
            <center><h3 style="text-align:center;">{{$assignment->version->name}}</h3></center>
            @if($assignment->version->type === 'articulate_tincan')
                @if(!isset($assignment->version->reference->filename))
                    <div class="alert alert-danger" style="text-align:center;align-content:center;margin:auto">
                        <div>The Articulate Launch URL has not been configured for this module version.</div>
                    </div>
                @elseif(!file_exists(config('filesystems.disks.local.root').'/public/modules/'.$assignment->module_id.'/versions/'.$assignment->module_version_id.'/'.$assignment->version->reference->filename))
                    <div class="alert alert-danger" style="text-align:center;align-content:center;margin:auto">
                        <div>The Articulate Launch URL "{{$assignment->version->reference->filename}}" can not be found.  Please confirm that the module version has been configured properly.</div>
                    </div>
                @else
                    <iframe style="border:0px;" width="100%" height="570" src="{{url('/storage/modules/'.$assignment->module_id.'/versions/'.$assignment->module_version_id)}}/{{$assignment->version->reference->filename}}?activity_id={{$assignment->id}}&endpoint={{url('/api/tincan')}}&auth=0&actor=<?php echo htmlentities(json_encode(["name"=>Auth::user()->first_name.' '.Auth::user()->last_name,"mbox"=>Auth::user()->email,'assignment_id'=>$assignment_id]));?>"></iframe>
                @endif
            @elseif($assignment->version->type === 'youtube')
                <div class="callout callout-info">{!! $assignment->version->reference->instructions !!}</div>
                <div id="player"></div>
                <script>
                var done = false;
                var warn_on_exit = function(event) {
                    if (!done) {
                        event.preventDefault();
                        event.returnValue = '';
                    }
                }
                window.addEventListener('beforeunload',warn_on_exit,true);
                var tag = document.createElement('script');
                tag.src = "https://www.youtube.com/iframe_api";
                var firstScriptTag = document.getElementsByTagName('script')[0];
                firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
                var player;
                function onYouTubeIframeAPIReady() {
                    player = new YT.Player('player', {
                        height: '570',
                        width: '100%',
                        videoId: '{{$assignment->version->reference->code}}',
                        events: {
                            'onReady': onPlayerReady,
                            'onStateChange': onPlayerStateChange
                        },
                        playerVars: { 'controls': @if(isset($assignment->version->reference->controls) && $assignment->version->reference->controls === true) 1 @else 0 @endif },
                    });
                }

                function onPlayerReady(event) {
                    $.ajax({
                        url: '/api/video/statements/{{$assignment->id}}?verb=in_progress',
                        method:'PUT',
                    });
                    $.ajax({
                        url: '/api/video/state/{{$assignment->id}}',
                        success: function(data) {
                            if (typeof data.time !== undefined) {
                                player.seekTo(data.time)
                            }
                            event.target.playVideo();
                        },
                    });

                    setInterval(function() {
                        if (player.getPlayerState() === YT.PlayerState.PLAYING) {
                            $.ajax({
                                url: '/api/video/state/{{$assignment->id}}',
                                data: {time:player.getCurrentTime()},
                                success: function() {},
                                method:'PUT',
                            });
                        }
                    }, 10000);
                }

                function onPlayerStateChange(event) {
                    if (event.data == YT.PlayerState.ENDED) {
                        $.ajax({
                            url: '/api/video/statements/{{$assignment->id}}?verb=completed',
                            data: {time:player.getCurrentTime()},
                            success: function() {
                                done = true;
                                alert("Thank you for completing this video.  You have now received credit for this training module.")
                                window.location = '/';
                            },
                            method:'PUT',
                        });
                    } else if (event.data == YT.PlayerState.PAUSED) {
                        // alert("This video is paused.  You must watch until the end in order to receive credit")
                    }
                }
                </script>
            @endif
        @endif
    @endif
    </div>
</div>
@endsection