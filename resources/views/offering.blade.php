@extends('default.default')
{{-- @push('styles')
    <link href="/assets/css/workshop.css" rel="stylesheet">
@endpush --}}
@section('title', 'Workshop Offering')

@section('content')

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><p>Workshop Offering Details</p></li>
        </ol>
    </nav>
    @if($workshop)
    <div class="container-fluid">

        <div class="container">
          <!-- Title -->
          <div class="d-flex justify-content-between align-items-center py-3">
            <h2 class="h5 mb-0"><a href="#" class="text-muted"></a> Workshop Offering #{{$offering->id}}</h2>
          </div>
         <!-- Bootstrap Card Copy  -->
         <div class="row">
            <div class="col-md-2">&nbsp;</div>
            <div class="col-md-8">
              <div class="row space-16">&nbsp;</div>
              <div class="row">
                <div class="col-md-12">
                  <div class="thumbnail">
                    <div class=" text-center" >
                      {{-- <div class="position-relative">
                        <img src="https://az818438.vo.msecnd.net/icons/slack.png" style="width:72px;height:72px;" />
                      </div> --}}
                      <h4 id="thumbnail-label"><p>{{$workshop->name}}</p></h4>
                      <p><i class="glyphicon glyphicon-user light-red lighter bigger-120"></i>&nbsp;{{$workshop->owner->first_name}} {{$workshop->owner->last_name}}</p>
                      @if($attendance)
                      <span class="label label-info">Status: {{$attendance->status}}</span>
                      @else
                      <span class="label label-warning">Status: Not Assinged </span>
                        @endif
                     
                      <span class="label label-primary">Public: {{$workshop->public}}</span>
                      <div class="thumbnail-description smaller" style="margin-top: 20px;" > {{$workshop->description}}</div>
                    </div>
                    <table class="table table-borderless">
                     
                        <tfoot>
                            <tr>
                            <td colspan="2">Date</td>
                            <td class="text-end">{{$offering->workshop_date}}</td>
                            </tr>
                          <tr>
                            <td colspan="2">Type</td>
                            <td class="text-end">{{$offering->type}}</td>
                          </tr>
                          <tr>
                            <td colspan="2">Location</td>
                            <td class="text-end">{{$offering->locations}}</td>
                          </tr>
                          <tr>
                            <td colspan="2">Instructor</td>
                            <td class="text-end">{{$offering->instructor->first_name }} {{$offering->instructor->last_name}}</td>
                          </tr>
                          <tr>
                            <td colspan="2">Max Capacity</td>
                            <td class="text-end">{{$offering->max_capacity}}</td>
                          </tr>
                        </tfoot>
                      </table>
                    <div class="caption card-footer text-center">
                      <ul class="list-inline">
                        {{-- <li><i class="people lighter"></i>&nbsp;7 Active Users</li> --}}
                        <li></li>
                        <li><i class="glyphicon glyphicon-envelope lighter"></i><a href="mailto:comply@binghamton.edu">&nbsp;Help</a></li>
                      </ul>
                      @if($attendance)
                      <a type="button" class="btn ">Assigned</a>
                      @else
                      <a href="/workshops/{{$workshop->id}}/offerings/{{$offering->id}}/assign" type="button" class="btn btn-success">Assign</a>
                        @endif
                    
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-2">&nbsp;</div>
            </div>
          </div>
         
        </div>
          </div>
    @else
        <div class="row" style="text-align: center;align-content: center;margin: auto;" >
            <div class="alert alert-warning">
                <h4 style="margin-top:0px;">There is no offering exists with this id!</h4>
                <div>Contact <a href="mailto:comply@binghamton.edu">comply@binghamton.edu</a> if you feel that this is an error.</div>
            </div>
        </div>
    @endif
@endsection