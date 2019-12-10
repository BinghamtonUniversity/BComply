<div class="container-fluid">
    <div>
        <h3>Hello {{$first_name}} {{$last_name}}</h3>
    </div>
    <div>
        <h2>Reminder</h2>
        <h4>You have an incomplete assignment</h4>
        <h4>Course Name: {{$user_message}}</h4>
        <h4>Due Date:{{$due_date}}</h4>
        @if($hours === 1)
            <h4>You have {{$hours}} hour left to complete your assignment</h4>
        @elseif($hours===0)
            <h4>You have {{$minutes}} minutes left to complete your assignment</h4>
        @elseif($hours>1 && ($minutes>0 && $minutes<60))
            <h4>You have {{$hours}} hours and {{$minutes}} minutes left to complete your assignment</h4>
        @else
            <h4>You have {{$hours}} hours left to complete your assignment</h4>
        @endif

    </div>

    You can access the course using <a href="http://localhost:8000/assignment/{{$link}}"> {{$user_message}}</a><br>
</div>
