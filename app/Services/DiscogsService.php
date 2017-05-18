<?php

namespace Services;

use Cache;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;

class DiscogsService
{
    /**
     * base api uri
     * @var string
     */
    protected $base_api_uri = 'https://api.discogs.com';

    /**
     * request token url
     * @var string
     */
    protected $request_token_url = 'https://api.discogs.com/oauth/request_token';

    /**
     * authorize url
     * @var string
     */
    protected $authorize_url = 'https://www.discogs.com/oauth/authorize';

    /**
     * access token url
     * @var string
     */
    protected $access_token_url = 'https://api.discogs.com/oauth/access_token';

    /**
     * cache length
     * @var integer
     */
    protected $cache_length = 120; // min

    /**
     * constructor
     */
    public function __construct()
    {
        $this->consumer_key = env('DISCOGS_KEY');
        $this->consumer_secret = env('DISCOGS_SECRET');
        $this->callback_url = env('DISCOGS_CALLBACK_URL');
    }

    /**
     * handle access token
     * @return redirect
     */
    public function handleAccessToken()
    {
        $oauth_token = isset($_GET['oauth_token']) ? $_GET['oauth_token'] : false;
        $oauth_verifier = isset($_GET['oauth_verifier']) ? $_GET['oauth_verifier'] : false;

        if (!$oauth_token || !$oauth_verifier) {
            return false;
        }

        $response = $this->client([
            'token' => $oauth_token,
            'token_secret' => Cache::get('discogs_oauth_token_secret'),
            'verifier' => $oauth_verifier,
        ])->request('POST', 'oauth/access_token', [
            'auth' => 'oauth',
        ]);

        $params = [];
        parse_str($response->getBody(), $params);

        if ($params) {
            Cache::put('discogs_oauth_token', $params['oauth_token'], $this->cache_length);
            Cache::put('discogs_oauth_token_secret', $params['oauth_token_secret'], $this->cache_length);
        }

        return redirect($this->callback_url);
    }

    /**
     * request token
     * @return redirect
     */
    public function requestToken()
    {
        $response = $this->client([
            'token' => '',
            'token_secret' => '',
            'callback' => $this->callback_url,
        ])->request('GET', 'oauth/request_token', [
            'auth' => 'oauth',
        ]);

        $params = [];
        parse_str($response->getBody(), $params);

        Cache::put('discogs_oauth_token', $params['oauth_token'], $this->cache_length);
        Cache::put('discogs_oauth_token_secret', $params['oauth_token_secret'], $this->cache_length);

        return redirect($this->authorize_url . '?oauth_token=' . $params['oauth_token']);
    }

    /**
     * set up client
     * @param  array  $options
     * @return boject
     */
    private function client($options = [])
    {
        $stack = HandlerStack::create();
        $middleware = $this->middleware($options);
        $stack->push($middleware);
        $config = $this->config($stack);
        return new Client($config);
    }

    /**
     * oauth middleware
     * @param  array  $options
     * @return object
     */
    private function middleware($options = [])
    {
        $default = [
            'consumer_key' => $this->consumer_key,
            'consumer_secret' => $this->consumer_secret,
        ];

        return new Oauth1(array_merge($default, $options));
    }

    /**
     * config
     * @param  object $stack
     * @return array
     */
    private function config($stack)
    {
        return [
            'base_uri' => $this->base_api_uri,
            'handler' => $stack,
            'headers' => [
                'User-Agent' => 'discogslovesspotify/1.0.0 +' . env('APP_URL'),
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
        ];
    }

    /**
     * parse response
     *
     * @param mixed $response
     * @return array
     */
    private function parse_response($response)
    {
        if (!$response) {
            return false;
        }
        return json_decode($response->getBody(), true);
    }

    /**
     * get authenticated username
     * @return string
     */
    public function getUserName()
    {
        $response = $this->client([
            'token' => Cache::get('discogs_oauth_token'),
            'token_secret' => Cache::get('discogs_oauth_token_secret'),
        ])->request('GET', 'oauth/identity', [
            'auth' => 'oauth',
        ]);

        $parsedResponse = $this->parse_response($response);

        return $parsedResponse['username'];
    }

    /**
     * get user's collection
     * @return array
     */
    private function userCollection($page = 1)
    {
        $response = $this->client([
            'token' => Cache::get('discogs_oauth_token'),
            'token_secret' => Cache::get('discogs_oauth_token_secret'),
        ])->request('GET', 'users/' . $this->getUserName() . '/collection', [
            'auth' => 'oauth',
            'query' => [
                'page' => $page,
            ]
        ]);

        return $this->parse_response($response);
    }

    /**
     * get list of user albums (formatted)
     * @return array
     */
    public function getUserAlbums()
    {
        $albums = [];
        $collection = $this->userCollection();
        $generalKey = 0;
        for ($k = 0 ; $k < $collection['pagination']['pages']; $k++) {
            if ($k > 0) {
                $collection = $this->userCollection($k + 1);
            }
            foreach ($collection['releases'] as $key => $release) :
                $albums[$generalKey]['title'] = $release['basic_information']['title'];
            $albums[$generalKey]['artist'] = $release['basic_information']['artists'][0]['name'];
            $generalKey++;
            endforeach;
        }

        return $albums;
    }
}
