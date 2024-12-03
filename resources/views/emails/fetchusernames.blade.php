@extends('layouts.email')

@section('content')
    <div class="container"
        style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif; font-size: 12px; font-weight:normal">
        <p>Poštovani/a,</p>
        <p>{{ $poruka }} </p>
        @if(count($usernames) > 0)
            @php
                $counter = 1;
            @endphp
            @foreach ($usernames as $username)
                <p>{{ $counter++ }}. {{ $username }}</p>
            @endforeach        
        @endif
        <p>Srdačno,<br/>Vaš Klett Srbija tim</p>        
    </div>
@endsection