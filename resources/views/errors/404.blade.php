@extends('../default.default')

@section('title', 'Not Found')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="alert alert-danger" style="text-align:center;align-content:center;margin:auto">
            <h3>Not Found</h3>
            <div>
                @if($exception)
                    {{ $exception->getMessage() }}
                @endif
            </div>
        </div>
    </div>
</div>
@endsection