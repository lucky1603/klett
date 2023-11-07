@extends('layouts.app')

@section('content')

    <div class="position-absolute h-100 w-100">
        <div class="d-flex align-items-center justify-content-center h-100 flex-column">
            <h1>Greška!</h1>
            <p>
                Vaši podaci nisu nađeni u našoj bazi korisnika. Molimo, izvršite registraciju na platformu
                na <a href="{{ route('appusers.register') }}">sledecem linku</a>.
            </p>
        </div>
    </div>

@endsection
