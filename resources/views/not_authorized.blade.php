@extends('default.default')

@section('title', 'Not Authorized')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="alert alert-danger" style="text-align:center;align-content:center;margin:auto">
            <h3>Not Authorized!</h3>
            <div>
                The BNumber "{{$bnumber}}" is not authorized to view this application.  
                If you beleive that this is in error, please contact
                <a href="https://www.binghamton.edu/uctd/">UCTD</a>
            </div>
        </div>
    </div>
</div>
@endsection