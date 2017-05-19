<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Cache;
use Services\DiscogsService as Discogs;
use Services\SpotifyService as Spotify;

class SiteController extends Controller
{
    protected $cache_length = 120; // min

    /**
     * constructor
     */
    public function __construct()
    {
        $this->discogs = app(Discogs::class);
        $this->spotify = app(Spotify::class);
    }

    /**
     * the index page
     * @return view
     */
    public function index()
    {
        $username = $this->discogs->getUserName();

        return view('home', compact('username'));
    }


    /**
     * discogs - get albums and artists
     * @return redirect
     */
    public function discogs()
    {
        // if not authorised, authorise
        if(!$this->discogs->getUserName()) {
            return redirect('/discogs/authorise');
        }

        $albums = Cache::get('spotify_albums');
        $artists = Cache::get('spotify_artists');

        // if there are no albums or artists already, get & cache them
        if (!$albums || !$artists) {
            $spotify_ids = [];
            $get_user_albums = $this->discogs->getUserAlbums();
            $spotify_ids = $this->spotify->getAlbumAndArtistIds($get_user_albums);

            $get_albums = array_chunk(array_unique($spotify_ids['albums']), 50);
            $get_artists = array_chunk(array_unique($spotify_ids['artists']), 50);

            Cache::put('spotify_albums', $get_albums, $this->cache_length);
            Cache::put('spotify_artists', $get_artists, $this->cache_length);
        }

        return redirect('/');
    }

    /**
     * request discogs authentication
     * @return mixed
     */
    public function discogsAuthorise()
    {
        return $this->discogs->requestToken();
    }

    /**
     * discogs callback
     * @return redirect
     */
    public function discogsCallback()
    {
        $this->discogs->handleAccessToken();

        return redirect('/');
    }

    public function spotify()
    {
    }

    /**
     * spotify authorise
     */
    public function spotifyAuthorise()
    {
        return $this->spotify->authorise();
    }

    /**
     * spotify callback
     * @return [type] [description]
     */
    public function spotifyCallback()
    {
        $this->spotify->handleCode();

        $albums = Cache::get('spotify_albums');

        foreach ($albums as $album_chunk) {
            $this->spotify->saveAlbumsToLibrary($album_chunk);
            sleep(1);
        }
    }
}
