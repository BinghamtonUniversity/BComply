<div class="container-fluid">
    <div>
        <h3>Hello {{$first_name}} {{$last_name}}</h3>
    </div>
    <div>
        <h4>You have successfully added the course {{$user_message}} to your assignments</h4>
    </div>

    You can access the course using <a href="{{url('/assignment/'.$link)}}"> {{$user_message}}</a><br>
</div>
