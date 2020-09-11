@extends('default.default')

@section('title', 'External User Login')

@section('content')
<div class="row">
    <div class="col-sm-12"">
        <center><h3 style="text-align:center;">External User Login</h1></center>
        <div class="row">
            <div class="col-sm-6 col-sm-offset-3">
                <div class="alert alert-danger">
                    This "external" login option is provided for users who have no direct affiliation
                    with Binghamton University (do not have a PODS username or BNumber).  
                    If you <i>do</i> have an affiliation with Binghamton University, please login 
                    <a href="/login">here</a>.
                </div>
                <div class="alert alert-info">
                    <div>Please log in using the "External User Account ID" you were given. </div>
                    <div style="margin-top:10px;">Note: Your ID should begin with "_ext_" </div>
                </div>
            </div>
            <div class="col-sm-6 col-sm-offset-3">
                <form action="external" method="post" style="margin-top:20px;">
                    @csrf <!-- {{ csrf_field() }} -->
                    @if(request()->has('redirect')) <input type="hidden" name="redirect" value="{{request()->redirect}}"> @endif
                    <div class="form-group">
                        <label for="accountId">External User Account ID</label>
                        <input type="input" class="form-control" id="accountId" name="accountId" placeholder="_ext_12345">
                    </div>
                    <button type="submit" class="btn btn-default">Submit</button>
                </form>
                @isset($error)
                    <div class="alert alert-danger" style="margin-top:20px;">
                        {{$error}}
                    </div>
                @endisset
            </div>
        </div>
    </div>
</div>
@endsection