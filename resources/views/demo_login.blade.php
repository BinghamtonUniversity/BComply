@extends('default.default')

@section('title', 'Select Demo User')

@section('content')
<div class="row">
    <div class="col-sm-12"">
        <center><h3 style="text-align:center;">BComply Guest Login</h1></center>
        <div class="row">
            <div class="col-sm-4 col-sm-offset-4">
                <form action="demo" method="post" style="margin-top:20px;">
                    @csrf <!-- {{ csrf_field() }} -->
                    @if(request()->has('redirect')) <input type="hidden" name="redirect" value="{{request()->redirect}}"> @endif
                    <div class="form-group">
                        <label for="accountId">Guest Account ID</label>
                        <input type="input" class="form-control" id="accountId" name="accountId" placeholder="_demo">
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