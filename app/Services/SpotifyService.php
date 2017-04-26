<?php

namespace Services;

use GuzzleHttp\Client;
// use GuzzleHttp\HandlerStack;
// use GuzzleHttp\Subscriber\Oauth\Oauth1;

class SpotifyService
{
    /**
     * base api uri
     * @var string
     */
    protected $base_api_uri = 'https://api.spotify.com';

    /**
     * authorize url
     * @var string
     */
    protected $authorize_url = 'https://accounts.spotify.com/authorize';

    /**
     * constructor
     */
    public function __construct()
    {
        $this->client_id = env('SPOTIFY_CLIENT');
        $this->client_secret = env('SPOTIFY_SECRET');
        $this->callback_url = env('APP_URL');
    }

    public function requestToken()
    {
        $response = $this->client()->request('GET', $this->authorize_url, $this->params());

        return $response->getBody();
    }

    /**
     * set up client
     * @param  array  $options
     * @return boject
     */
    private function client()
    {
        return new Client($this->config());
    }

    /**
     * config
     * @param  object $stack
     * @return array
     */
    private function config()
    {
        return [
            'base_uri' => $this->base_api_uri,
            'headers' => [
                'User-Agent' => 'discogslovesspotify/1.0.0 +' . env('APP_URL'),
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
        ];
    }

    /**
     * config
     * @param  object $stack
     * @return array
     */
    private function params()
    {
        return [
            'query' => [
                'client_id' => $this->client_id,
                'response_type' => 'code',
                'state' => '', // todo
                'show_dialog' => true
            ]
        ];
    }
}
