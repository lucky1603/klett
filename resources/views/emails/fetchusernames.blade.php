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
            <p>
            @foreach ($usernames as $username)
                {{ $counter++ }}. {{ $username }}<br />
            @endforeach     
            </p>   
        @endif
        <p>Ovo je automatski generisana poruka. Za tehničku podršku pišite nam na <a href="mailto:tehnicka.podrska@klett.rs" target="_blank">tehnicka.podrska@klett.rs</a>.</p>
        <p>Srdačno,<br/>Vaš Klett Srbija tim</p>        
    </div>
@endsection