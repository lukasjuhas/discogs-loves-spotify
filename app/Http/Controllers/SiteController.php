<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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

        $album_ids = [];
        $artist_ids = [];
        $albums = $this->discogs->getUserAlbums();
        // dd($albums);
        $search = $this->spotify->searchAlbums($albums[10]);
        dd($search);
        foreach($albums as $album) {
            $search = $this->spotify->searchAlbums($album);
            if(isset($search[0])) {
                $album_ids[] = $search[0]['id'];
                $artist_ids[] = $search[0]['artists'][0]['id'];
            }

            sleep(1);
        }

        print_r($album_ids);
        print_r($artist_ids);
        die();

        // $username = $this->discogs->getUserName();
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
        return $this->spotify->requestToken();
    }
}
