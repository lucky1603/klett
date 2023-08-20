@extends('layouts.email')

@section('content')

    @php
        $token = $user->getAttribute('remember_token');
    @endphp

    <div class="container"
        style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif; font-size: 12px; font-weight:normal">
        <p>Poštovani,</p>
        <p>U našoj aplikaciji je kreiran korisnički nalog sa Vašom email adresom.</p>
        <p>Da biste mogli da koristite aplikaiju, moimo Vas za kliknete na sledeci link za potvrdu - <a href="{{ route('anonimous.verify', ['token' => $token]) }}"> {{ route('anonimous.verify', ['token' => $token]) }} </a>

    </div>
</p>
@endsection
