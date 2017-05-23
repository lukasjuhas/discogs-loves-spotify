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
     * @return view
     */
    public function index()
    {
        $spotifyUsername = $this->spotify->getUserName();
        $discogsUsername = $this->discogs->getUserName();

        return view('home', compact('discogsUsername', 'spotifyUsername'));
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

        $albums = Session::get('spotify_albums');
        $artists = Session::get('spotify_artists');

        // if there are no albums or artists already, get & save them in to session
        if (!$albums || !$artists) {
            $spotify_ids = [];
            $get_user_albums = $this->discogs->getUserAlbums();
            $spotify_ids = $this->spotify->getAlbumAndArtistIds($get_user_albums);

            $get_albums = array_chunk(array_unique($spotify_ids['albums']), 50);
            $get_artists = array_chunk(array_unique($spotify_ids['artists']), 50);

            Session::put('spotify_albums', $get_albums);
            Session::put('spotify_artists', $get_artists);
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
        $albums = Session::get('spotify_albums');

        foreach ($albums as $album_chunk) {
            $this->spotify->saveAlbumsToLibrary($album_chunk);
            sleep(1); // play nice with request limiting
        }

        return redirect('/');
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

        return redirect('/');
    }

    public function sync(Request $request)
    {
        $sync_albums = $request->get('albums') ? true : false;
        $sync_artists = $request->get('artists') ? true : false;

        $discogs = $this->handleDiscogs();

        if($sync_albums) {
            $albums = $discogs['albums'];
            foreach ($albums as $album_chunk) {
                $this->spotify->saveAlbumsToLibrary($album_chunk);
                sleep(1); // play nice with request limiting
            }
        }

        if($sync_artists) {
            $artists = $discogs['artists'];
            foreach ($artists as $artist_chunk) {
                $this->spotify->followArtists($artist_chunk);
                sleep(1); // play nice with request limiting
            }
        }

        return redirect('/')->with('flash', [
            'type' => 'success',
            'message' => 'Succesfully synced!',
        ]);
    }

    /**
     * handle discogs
     * @return array
     */
    private function handleDiscogs()
    {
        $discogs = [];
        $albums = Session::get('spotify_albums');
        $artists = Session::get('spotify_artists');

        // if there are no albums or artists already, get & save them into session
        if (!$albums || !$artists) {
            $spotify_ids = [];
            $get_user_albums = $this->discogs->getUserAlbums();
            $spotify_ids = $this->spotify->getAlbumAndArtistIds($get_user_albums);

            $get_albums = array_chunk(array_unique($spotify_ids['albums']), 50);
            $get_artists = array_chunk(array_unique($spotify_ids['artists']), 50);

            $albums = Session::put('spotify_albums', $get_albums);
            $artists = Session::put('spotify_artists', $get_artists);
        }

        $discogs['albums'] = $albums;
        $discogs['artists'] = $artists;

        return $discogs;
    }
}
