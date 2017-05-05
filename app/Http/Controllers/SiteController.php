<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Session;
use Services\DiscogsService as Discogs;
use Services\SpotifyService as Spotify;

class SiteController extends Controller
{
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
     * @return [type] [description]
     */
    public function index()
    {
        $this->discogs->handleAccessToken();
        $this->spotify->handleCode();

        $spotify_ids = [];
        $albums = $this->discogs->getUserAlbums();

        $spotify_ids = $this->spotify->getAlbumAndArtistIds($albums);

        $albums = array_chunk(array_unique($spotify_ids['albums']), 50);
        $artists = array_chunk(array_unique($spotify_ids['artists']), 50);

        Session::put('spotify_albums', $albums);
        Session::put('spotify_artists', $artists);

        redirect('/spotify');

        die();

        $username = '';
        $username = $this->discogs->getUserName();
        // print_r($this->spotify->getAlbums());
        // die();

        return view('home', compact('username'));
    }

    /**
     * request discogs authentication
     * @return mixed
     */
    public function discogs()
    {
        return $this->discogs->requestToken();
    }

    public function spotify()
    {

    }

    public function spotifyAuthorise()
    {
        return $this->spotify->authorise();
    }

    public function spotifyCallback()
    {
        $this->spotify->handleCode();
        $this->spotify->requestToken();

        $albums = Session::get('spotify_albums');

        foreach ($albums as $album_chunk) {
            $this->spotify->saveAlbumsToLibrary($album_chunk);
            sleep(1);
        }
    }
}
