@extends('default.default')

@section('title', 'Google Calendar')

@section('content')
    @if(!is_null(Auth::user()))
        {{-- <div id="shoppingDataGrid"></div> --}}
    @endif


    @if(count($events)>0)
        <div class="row">
            @foreach ($events as $event)
            <div> <a>{{$event->summary}}</a>  </div>
            @endforeach
        </div>
    @else
        <div class="row" style="text-align: center;align-content: center;margin: auto;" >
            <div class="alert alert-warning">
                <h4 style="margin-top:0px;">You do not have any assigned workshops!</h4>
                <div>Contact <a href="mailto:comply@binghamton.edu">comply@binghamton.edu</a> if you feel that this is an error.</div>
            </div>
        </div>
    @endif
@endsection