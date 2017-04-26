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
        $oauth_token = isset($_GET['oauth_token']) ? $_GET['oauth_token'] : false;
        $oauth_verifier = isset($_GET['oauth_verifier']) ? $_GET['oauth_verifier'] : false;

        if($oauth_token || $oauth_verifier) :
            $stack = HandlerStack::create();

            $middleware = new Oauth1([
                'consumer_key'    => env('DISCOGS_KEY'),
                'consumer_secret' => env('DISCOGS_SECRET'),
                'token'           => $oauth_token,
                'token_secret'    => session('discogs.oauth_token_secret'),
                'verifier'        => $oauth_verifier,
                // 'signature_method'=> 'PLAINTEXT'
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
            // dd($client);
            $response = $client->request('POST', 'https://api.discogs.com/oauth/access_token', [
                'auth' => 'oauth',
            ]);

            $params = [];
            parse_str($response->getBody(), $params);

            if($params) {
                session([
                    'discogs' => [
                        'oauth_token' => $params['oauth_token'],
                        'oauth_token_secret' => $params['oauth_token_secret'],
                    ]
                ]);
            }
        endif;

        return view('home');
    }

    public function discogs()
    {
        $stack = HandlerStack::create();

        $middleware = new Oauth1([
            'consumer_key'    => env('DISCOGS_KEY'),
            'consumer_secret' => env('DISCOGS_SECRET'),
            'token'           => '',
            'token_secret'    => '',
            'callback'        => 'http://discogslovesspotify.dev',
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
            'auth' => 'oauth',
        ]);

        $params = [];
        parse_str($response->getBody(), $params);

        session(['discogs' => [
            'oauth_token' => $params['oauth_token'],
            'oauth_token_secret' => $params['oauth_token_secret'],
        ]]);

        return redirect('https://discogs.com/oauth/authorize?oauth_token=' . $params['oauth_token']);

    }

    public function getCollection()
    {
        $username = $this->getUserName();

        $stack = HandlerStack::create();

        $middleware = new Oauth1([
            'consumer_key'    => env('DISCOGS_KEY'),
            'consumer_secret' => env('DISCOGS_SECRET'),
            'token'           => session('discogs.oauth_token'),
            'token_secret'    => session('discogs.oauth_token_secret'),
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

        $response = $client->request('GET', 'https://api.discogs.com/users/' . $username . '/collection', [
            'auth' => 'oauth',
        ]);

        $responseBody = json_decode($response->getBody());

        return $responseBody;
    }

    public function getUserName()
    {
        $stack = HandlerStack::create();

        $middleware = new Oauth1([
            'consumer_key'    => env('DISCOGS_KEY'),
            'consumer_secret' => env('DISCOGS_SECRET'),
            'token'           => session('discogs.oauth_token'),
            'token_secret'    => session('discogs.oauth_token_secret'),
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

        $response = $client->request('GET', 'https://api.discogs.com/oauth/identity', [
            'auth' => 'oauth',
        ]);

        $responseBody = json_decode($response->getBody());

        return $responseBody->username;
    }
}
