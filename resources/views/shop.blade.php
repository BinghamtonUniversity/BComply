@extends('default.default')

@section('title', 'Shop')

@section('content')
    @if(!is_null(Auth::user()))
    <div id="shoppingDataGrid"></div>

    @endif

@endsection
{{--<!-- Begin Modal -->--}}
{{--<div class="modal fade" id="adminModal" tabindex="-1" role="dialog">--}}
{{--    <div class="modal-dialog" role="document">--}}
{{--        <div class="modal-content">--}}
{{--            <div class="modal-header">--}}
{{--                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>--}}
{{--                <h4 class="modal-title">Modal title</h4>--}}
{{--            </div>--}}
{{--            <div class="modal-body">--}}
{{--                <p>One fine body&hellip;</p>--}}
{{--            </div>--}}
{{--            <div class="modal-footer">--}}
{{--                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>--}}
{{--            </div>--}}
{{--        </div><!-- /.modal-content -->--}}
{{--    </div><!-- /.modal-dialog -->--}}
{{--</div><!-- /.modal -->--}}
{{--<!-- End Modal -->--}}

