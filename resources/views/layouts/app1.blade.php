<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <!-- Localization -->
    <script src="/lang-{{ app()->getLocale() }}.js"></script>
</head>
<body>
    <div id="app">
        <!-- Header with logos -->
        <div class="container-fluid d-flex align-items-center justify-content-center" style="height: 200px; background-color:coral">
            <img src="/images/klett1.png" style="height: 60px; margin: 20px"/>
            <img src="/images/logos.png" style="height: 60px; margin: 20px"/>
            <img src="/images/freska.png" style="height: 60px; margin: 20px"/>
            <img src="/images/eucilogo1.png" style="height: 60px; margin: 20px"/>
            <img src="/images/eucionica.png" style="height: 60px; margin: 20px"/>
            <img src="/images/eknjizaralogo.svg" style="height: 60px; margin: 20px"/>
        </div>

        <main class="py-4">
            @yield('content')
        </main>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>

</body>
</html>
