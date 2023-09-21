@extends('layouts.email')

@section('content')
    <div class="container"
        style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif; font-size: 12px; font-weight:normal">
        <p>Poštovani/a {{ $user }},</p>
        <p>U našoj novoj platformi za jedinstvenu prijavu je kreiran korisnički nalog sa Vašom email adresom.</p>
        <p>Da biste mogli da koristite sve pogodnosti jedinstvene platforme, najlepše Vas molimo da upotpunite Vaše
            podatke posetom sledećeg linka -
            <a href="{{ route('remoteusers.scheduledEdit', ['token' => $token]) }}"> {{ route('remoteusers.scheduledEdit', ['token' => $token]) }} </a>
    </div>
@endsection
