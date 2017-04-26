<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;

class SiteController extends Controller
{
    public function index()
    {
        return view('home');
    }

    public function discogs()
    {
        $stack = HandlerStack::create();

        $middleware = new Oauth1([
            'consumer_key'    => env('DISCOGS_KEY'),
            'consumer_secret' => env('DISCOGS_SECRET'),
            'token'           => '',
            'token_secret'    => ''
        ]);

        $stack->push($middleware);

        $config = [
            'handler' => $stack,
            'headers' => [
                'User-Agent' => 'discogslovesspotify/1.0.0 +http://discogslovesspotify.dev',
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
        ];

        $client = new Client($config);
        $response = $client->request('GET', 'https://api.discogs.com/oauth/request_token', [
            'auth' => 'oauth'
        ]);

        $params = [];
        parse_str($response->getBody(), $params);

        print_r($params);
    }
}
