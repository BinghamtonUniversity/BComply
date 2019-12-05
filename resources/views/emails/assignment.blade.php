<div class="container-fluid">
    <div>
        <h3>Hello {{$first_name}} {{$last_name}}</h3>
    </div>
    <div>
        <h4>You are assigned to a new course: {{$user_message}}</h4>
        <h4>Due Date:{{$due_date}}</h4><br>
    </div>

    You can access the course using <a href="http://localhost:8000/assignment/{{$link}}"> {{$user_message}}</a><br>
</div>
