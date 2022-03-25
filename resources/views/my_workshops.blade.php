@extends('default.default')

@section('title', 'My Workshops')

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">My Current Workshops</a></li>
        </ol>
    </nav>
    @if(count($workshops)>0)
        <div class="row">
            @foreach ($workshops as $workshop)
                @include('workshop', ['workshop' => $workshop])
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