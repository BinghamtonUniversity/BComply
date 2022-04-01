@extends('default.default')
{{-- @push('styles')
    <link href="/assets/css/workshop.css" rel="stylesheet">
@endpush --}}
@section('title', 'Workshop Offering')
<style>
    /* The Modal (background) */
.modal {
  display: none; /* Hidden by default */
  position: fixed; /* Stay in place */
  z-index: 1; /* Sit on top */
  left: 0;
  top: 0;
  width: 100%; /* Full width */
  height: 100%; /* Full height */
  overflow: auto; /* Enable scroll if needed */
  background-color: rgb(0,0,0); /* Fallback color */
  background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
}

/* Modal Content/Box */
.modal-content {
  background-color: #fefefe;
  margin: 15% auto; /* 15% from the top and centered */
  padding: 20px;
  border: 1px solid #888;
  width: 80%; /* Could be more or less, depending on screen size */
}

/* The Close Button */
.close {
  color: #aaa;
  float: right;
  font-size: 28px;
  font-weight: bold;
}

.close:hover,
.close:focus {
  color: black;
  text-decoration: none;
  cursor: pointer;
}
</style>
@section('content')
    @if(!$attendance && !$workshop->public )
    <div class=" text-center" >
        <div class="alert" style=" padding: 20px;
        background-color: #f44336;
        color: white;">
            <span class="closebtn" style=" margin-left: 15px;
            color: white;
            font-weight: bold;
            float: right;
            font-size: 22px;
            line-height: 20px;
            cursor: pointer;
            transition: 0.3s;" onclick="this.parentElement.style.display='none';">&times;</span> 
            <strong>403</strong> Forbidden!
          </div>
       
    </div>
    @else
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
                        <span class="label label-info">Status: {{$attendance->attendance}}</span>
                        @else
                        <span class="label label-warning">Status: Not Assinged </span>
                            @endif
                        @if($workshop->public)
                        <span class="label label-primary">Public</span>
                        @else
                        <span class="label label-primary">Private</span>
                        @endif
                        
                        <div class="thumbnail-description smaller" style="margin-top: 20px;" > {{$workshop->description}}</div>
                        </div>
                        <table class="table table-borderless">
                        
                            <tfoot>
                                @if($is_past)
                                    <tr>
                                    <td colspan="2" class="text-danger">Date</td>
                                    <td class="text-end text-danger">{{$offering->workshop_date}}</td>
                                    </tr>
                                @else
                                    <tr>
                                    <td colspan="2">Date</td>
                                    <td class="text-end">{{$offering->workshop_date}}</td>
                                    </tr>
                                @endif

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
                            <tr>
                                @if($seats_remaining <= 0 )
                                    <td colspan="2" class="text-danger">Remaining Available Seats</td>
                                    <td class="text-end text-danger">{{$seats_remaining}}</td>
                                @else
                                    <td colspan="2">Remaining Available Seats</td>
                                    <td class="text-end">{{$seats_remaining}}</td>
                                @endif
                             
                            </tr>
                            </tfoot>
                        </table>
                        <div class="caption card-footer text-center">
                        <ul class="list-inline">
                            {{-- <li><i class="people lighter"></i>&nbsp;7 Active Users</li> --}}
                            <li></li>
                            <li><i class="glyphicon glyphicon-envelope lighter"></i><a href="mailto:comply@binghamton.edu">&nbsp;Help</a></li>
                        </ul>
                        @if($is_past)
                            <a type="button" class="btn btn-default">Workshop is not Available anymore!</a>
                        @else
                            @if($attendance)
                            <button  type="button" id="myBtn" class="btn btn-danger">Cancel Registration</button>
                            <div id="cancelModal" class="modal">
                                <!-- Modal content -->
                                <div class="modal-content">
                                    <span class="close">&times;</span>
                                    <p>Are you sure canceling this registration?</p>
                                    <a  type="button" id="closeBtn" class="btn btn-default">Close</a>
                                    <a href="/workshops/{{$workshop->id}}/offerings/{{$offering->id}}/cancelRegistration" type="button" id="myBtn" class="btn btn-danger">Cancel Registration</a>
                                </div>
                            </div>
                            @else

                                @if($seats_remaining <= 0 )
                                <a  type="button" class="btn btn-default">No Seats Available</a>
                                @else
                                <a href="/workshops/{{$workshop->id}}/offerings/{{$offering->id}}/assign" type="button" class="btn btn-success">Register to Workshop</a>
                                @endif
                            @endif
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
    @endif
    <script>
        // Get the modal
    var modal = document.getElementById("cancelModal");
    
    // Get the button that opens the modal
    var btn = document.getElementById("myBtn");
    var closeBtn = document.getElementById("closeBtn")
    // Get the <span> element that closes the modal
    var span = document.getElementsByClassName("close")[0];
    
    // When the user clicks on the button, open the modal
    btn.onclick = function() {
      modal.style.display = "block";
    }
    
    // When the user clicks on <span> (x), close the modal
    span.onclick = function() {
      modal.style.display = "none";
    }
    closeBtn.onclick = function() {
      modal.style.display = "none";
    }
    
    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function(event) {
      if (event.target == modal) {
        modal.style.display = "none";
      }
    }
    </script> 
@endsection
