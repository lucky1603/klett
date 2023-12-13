<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>REGISTRACIJA</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <!-- Localization -->
    <script src="/lang-{{ app()->getLocale() }}.js"></script>
</head>
<body class="applyform w-100">
    <div id="app">
        <!-- Header with logos -->
        <div class="shadow bg-light" style="width: 100%">
            <div class="container-fluid px-0">
                <div class="row w-100 mx-0">
                    <div class="col-12 offset-sm-2 col-sm-4">
                        <img src="/images/Header_KLF-logotipi.svg" class="float-xl-right float-center my-4" style="width: 100%;"/>
                    </div>
                    <div class="col-12 col-sm-4" style="background-color: #efefef">
                        <img src="/images/Header_EEE-logotipi.svg" class="float-xl-left float-center my-4" style="width: 100%; "/>
                    </div>
                    <div class="col-sm-2" style="background-color: #efefef"></div>
                </div>
            </div>

        </div>


        <main class="py-4">
            @yield('content')
        </main>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>

</body>
</html>
