@extends('layouts.app')

@section('content')
    <section class="section">
        <div class="content">
            <div class="container">
                <p>Sync your Discogs collection with Spotify. This tool searches goes through your vinyl collection and saves every record that is available on Spotify to your library.</p>
            </div>
            <nav class="steps">
                <ul>
                    <li class="steps__item steps__item--discogs">
                        @if($username)
                            <div class="step__item__link">
                        @else
                            <a class="step__item__link" href="{{ url('discogs/authorise') }}">
                        @endif
                            <p><strong>1. Connect to Discogs</strong></p>
                            <p>{!! file_get_contents(public_path('images/discogs.svg')) !!}</p>
                            @if($username)
                                <p>✅ Signed in as {{ $username }}.</p>
                            @endif
                        @if($username)
                            </div>
                        @else
                            </a>
                        @endif
                    </li>
                    <li class="steps__item steps__item--spotify">
                        <a class="step__item__link" href="{{ url('spotify/authorise') }}">
                            <p><strong>2. Connect to Spotify</strong></p>
                            {!! file_get_contents(public_path('images/spotify.svg')) !!}
                        </a>
                    </li>
                    <li class="steps__item steps__item--spotify">
                        <a class="step__item__link" href="#0">
                            <p><strong>3. What wold you like to sync?</strong></p>
                            <div class="form__group">
                                <label>
                                    <input type="checkbox" value="1" name="albums" checked="checked">
                                    Albums
                                </label>
                            </div>
                            <div class="form__group">
                                <label>
                                    <input type="checkbox" value="1" name="albums"  checked="checked">
                                    Artists
                                </label>
                            </div>
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
