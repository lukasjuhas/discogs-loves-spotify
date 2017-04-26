@extends('layouts.app')

@section('content')
    <section class="section section--vcenter">
        <div class="content">
            <nav class="nav nav--steps">
                <ul>
                    <li>
                        <a href="#0">Connect to Discogs</a>
                    </li>
                    <li>
                        <a href="#0">Connect to Spotify</a>
                    </li>
                    <li>
                        Options
                        <button type="submit" class="button button--primary">Sync</button>
                    </li>
                </ul>
            </nav>
        </div>
    </section>
@endsection
