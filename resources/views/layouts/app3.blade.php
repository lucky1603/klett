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
</head>
<body class="position-absolute h-100 w-100">
    <div id="app" class="w-100 h-100">
        <b-row class="h-100">
            <b-col lg="4" xl="3">
                <img src="/images/klett.png" class="w-100 m-2">
                <div class="h-100 d-flex align-items-center justify-content-center flex-column">
                    @yield('content')
                </div>
            </b-col>
            <b-col lg="8" xl="9">
                <div style="width: 100%; height: 100%; background-image: url('images/books.jpeg'); background-repeat:no-repeat; background-attachment:fixed; background-size:cover"></div>
            </b-col>
        </b-row>
    </div>
    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
