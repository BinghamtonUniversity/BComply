<div class="container-fluid">
    <div>
        <h3>Hello {{$first_name}} {{$last_name}}</h3>
    </div>

        @if(($assignment->status==='completed')||($assignment->status==='passed'))
            <div>
                <h4>You have completed the course: {{$user_message}}</h4>
                <p>Status: {{$assignment->status}}</p>
            </div>
            You can access the certificate using   <a href="http://localhost:8000/assignment/{{$assignment->id}}/certificate"> {{$user_message}}</a><br>
        @else
        <div>
            <h4>You have completed the course: {{$user_message}}</h4>
        </div>
        @endif
</div>