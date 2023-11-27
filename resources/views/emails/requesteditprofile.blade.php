@extends('layouts.email')

@section('content')
    <div class="container"
        style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif; font-size: 12px; font-weight:normal">
        <p>Poštovani/a {{ $user }},</p>
        <p>Na osnovu Vašeg zahteva za izmenu podataka, kreirali smo za vas jednokratni
            <a href="{{ route('anonimous.scheduledEdit', ['token' => $token]) }}"> link na našu aplikaciju</a>.
        </p>
        <p>Posetom ovog linka dobićete formular za izmenu Vaših profilnih podataka. Posle uspešne izmene, ovaj link postaje neaktivan.
            Za ponovnu promenu podataka neophodno je da kreirate novi zatev za izmenu podataka.</p>

    </div>
@endsection
