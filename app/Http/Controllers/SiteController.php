<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;

use Services\DiscogsService as Discogs;

class SiteController extends Controller
{
    public function __construct()
    {
        $this->discogs = app(Discogs::class);
    }

    public function index()
    {
        $this->discogs->handleAccessToken();

        $username = $this->discogs->getUserName();

        return view('home', compact('username'));
    }

    public function discogs()
    {
        return $this->discogs->requestToken();
    }
}
