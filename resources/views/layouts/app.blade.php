<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Discogs ❤️ Spotify') }} - Sync your Discogs collection with your Spotify library.</title>

        <meta name="twitter:card" content="summary">
        <meta name="twitter:site" content="@itslukasjuhas">
        <meta name="twitter:domain" content="itsluk.as">
        <meta name="twitter:creator" content="@itslukasjuhas">
        <meta name="twitter:title" content="{{ config('app.name', 'Discogs ❤️ Spotify') }}">
        <meta name="twitter:image" content="/icon.png">

        <link rel="shortcut icon" href="/icon.png">
        <link rel="apple-touch-icon-precomposed" href="/icon.png">
        <meta name="apple-mobile-web-app-title" content="Discogs ❤️ Spotify">
        <meta name="description" content="Sync your Discogs collection with your Spotify library.">
        <link rel="canonical" href="{{ url('/') }}">
        <meta name="theme-color" content="#C81F40">
        <link rel="manifest" href="/manifest.json">

        <meta name="csrf-token" content="{{ csrf_token() }}">

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
                <p>Desclimer: This tool and website does not and will never store any of your information or your music for any purposes in order to make profit. It's build purely for one and only purpose which is making it possible to easily sync your Discogs collection with Spotify library.</p>
            </div>
            <div>
                <p>by <a href="https://itsluk.as" target="_blank" rel="noopener">Lukas Juhas</a>. Source code on <a href="https://github.com/lukasjuhas/discogs-loves-spotify">Github</a>.</p>
            </div>
        </footer>
        <script>
          (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
          (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
          m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
          })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

          ga('create', 'UA-85525050-3', 'auto');
          ga('send', 'pageview');

        </script>
    </body>
</html>
