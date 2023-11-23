@extends('layouts.email')

@section('content')

    <div class="container"
        style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif; font-size: 12px; font-weight:normal">
        <p>Poštovani/a {{ $user }},</p>
        <p>Resetujte Vašu lozinku!</p>
    </div>

@endsection
