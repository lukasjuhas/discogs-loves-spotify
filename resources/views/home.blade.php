@extends('layouts.app')

@section('content')
    <section class="section">
        <div class="content">
            <div class="container">
                {{ $username }}
                <p>Sync your Discogs collection with Spotify. This tool searches goes through your vinyl collection and saves every record that is available on Spotify to your library.</p>
            </div>
            <nav class="steps">
                <ul>
                    <li class="steps__item steps__item--discogs">
                        <a class="step__item__link" href="#0">
                            <p>1. Connect to Discogs</p>
                            {!! file_get_contents(public_path('images/discogs.svg')) !!}
                        </a>
                    </li>
                    <li class="steps__item steps__item--spotify">
                        <a class="step__item__link" href="#0">
                            <p>2. Connect to Spotify</p>
                            {!! file_get_contents(public_path('images/spotify.svg')) !!}
                        </a>
                    </li>
                    <li class="steps__item">
                        <button type="submit" class="button button--block">Sync Now 🚀</button>
                    </li>
                </ul>
            </nav>
        </div>
    </section>
@endsection
