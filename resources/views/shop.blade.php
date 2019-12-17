@extends('default.default')

@section('title', 'Shop')

@section('content')
    @if(!is_null(Auth::user()))
        <div id="shoppingDataGrid"></div>
    @endif
@endsection