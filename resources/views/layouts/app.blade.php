<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Discogs ❤️ Spotify') }}</title>

        <style>
            {!! file_get_contents(public_path('styles/app.css')) !!}
        </style>

        <script>
            window.Laravel = {!! json_encode([
                'csrfToken' => csrf_token(),
            ]) !!};
        </script>
    </head>

    <body>
        <div class="overlay"></div>
        <header class="header">
            <div class="container">
                <h1>Discogs ❤️ Spotify</h1>
            </div>
        </header>
        <main class="main">
            @yield('content')
        </main>
        <footer class="footer">
            <div class="container container--wide">
                <p>Desclimer: This tool and website does not and will never store any of your information or your music for any purposes in order to make profit. It's build purely for one and only purpose which is making it possible to easily sync your Discogs library with Spotify.</p>
            </div>
        </footer>
        <script src="{{ asset('scripts/app.js') }}"></script>
    </body>
</html>
