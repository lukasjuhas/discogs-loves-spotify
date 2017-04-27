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
        $this->spotify->handleAccessToken();

        $username = $this->discogs->getUserName();

        // dd($this->spotify->getAlbums());

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
