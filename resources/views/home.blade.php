@extends('layouts.app')

@section('content')
    <section class="section">
        <div class="content">
            <div class="container">
                <p>Sync your Discogs collection with your Spotify library. This tool goes through your vinyl collection and saves every record and follows every artist that is available on Spotify.</p>
            </div>
            <form action="{{ url('/sync') }}" method="POST" id="sync-form">
                {{ csrf_field() }}
                <nav class="steps">
                    <ul>
                        <li class="steps__item steps__item--discogs">
                            @if($discogsUsername)
                                <div class="step__item__link">
                            @else
                                <a class="step__item__link" href="{{ url('discogs/authorise') }}">
                            @endif
                                <p><strong>1. Connect to Discogs</strong></p>
                                <p>{!! file_get_contents(public_path('images/discogs.svg')) !!}</p>
                                @if($discogsUsername)
                                    <p>✅ Signed in as {{ $discogsUsername }}.</p>
                                @endif
                            @if($discogsUsername)
                                </div>
                            @else
                                </a>
                            @endif
                        </li>
                        <li class="steps__item steps__item--spotify">
                            @if($spotifyUsername)
                                <div class="step__item__link">
                            @else
                                <a class="step__item__link" href="{{ url('spotify/authorise') }}">
                            @endif
                                <p><strong>2. Connect to Spotify</strong></p>
                                <p>{!! file_get_contents(public_path('images/spotify.svg')) !!}</p>
                                @if($spotifyUsername)
                                    <p>✅ Signed in as {{ $spotifyUsername }}.</p>
                                @endif
                            @if($spotifyUsername)
                                </div>
                            @else
                                </a>
                            @endif
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
                                        <input type="checkbox" value="1" name="artists"  checked="checked">
                                        Artists
                                    </label>
                                </div>
                            </a>
                        </li>
                        <li class="steps__item">
                            <button type="submit" onClick="this.disabled=true; this.innerHTML='Syncing...'; document.getElementById('sync-form').submit();" class="button button--block" @if(!$spotifyUsername && !$discogsUsername) disabled="disabled" @endif>Sync Now 🚀</button>
                        </li>
                    </ul>
                </nav>
            </form>
        </div>
        <a target="_blank" title="Share on Twitter" class="twitter-share-button" href="https://twitter.com/intent/tweet?text=Discogs+%E2%9D%A4%EF%B8%8F++Spotify+-+Sync+your+Discogs+collection+with+your+Spotify+library.+%23DiscogsLovesSpotify">
        {!! file_get_contents(public_path('images/twitter.svg')) !!}</a>
    </section>
@endsection
