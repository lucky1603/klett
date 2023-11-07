@extends('layouts.app')

@section('content')
    <h1>Greška!</h1>
    <p>
        Vaši podaci nisu nađeni u našoj bazi korisnika. Molimo, izvršite registraciju na platformu
        na <a href="{{ route('appusers.register') }}">sledecem linku</a>.
    </p>
@endsection
