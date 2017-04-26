<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Discogs ❤️ Spotify') }}</title>

        <style>
            {!! file_get_contents(public_path('styles/core.css')) !!}
        </style>

        <link href="{{ asset('styles/app.css') }}" rel="stylesheet">

        <script>
            window.Laravel = {!! json_encode([
                'csrfToken' => csrf_token(),
            ]) !!};
        </script>
    </head>

    <body>
        <div class="overlay"></div>
        <div class="app">

            @yield('content')
        </div>

        <script src="{{ asset('scripts/app.js') }}"></script>
    </body>
</html>
