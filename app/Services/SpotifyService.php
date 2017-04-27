<?php

namespace Services;

use GuzzleHttp\Client;
// use GuzzleHttp\HandlerStack;
// use GuzzleHttp\Subscriber\Oauth\Oauth1;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

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

    protected $token_url = 'https://accounts.spotify.com/api/token';

    /**
     * constructor
     */
    public function __construct()
    {
        $this->client_id = env('SPOTIFY_CLIENT');
        $this->client_secret = env('SPOTIFY_SECRET');
        $this->callback_url = env('APP_URL');

        $this->provider = new GenericProvider([
            'clientId'                => $this->client_id,    // The client ID assigned to you by the provider
            'clientSecret'            => $this->client_secret,   // The client password assigned to you by the provider
            'redirectUri'             => $this->callback_url,
            'urlAuthorize'            => $this->authorize_url,
            'urlAccessToken'          => $this->token_url,
            'urlResourceOwnerDetails' => '',
        ]);
    }

    public function requestToken()
    {
        $authorizationUrl = $this->provider->getAuthorizationUrl();
        $authorizationUrl = $authorizationUrl . '&scope=' . urlencode('user-library-read user-library-modify');
        return redirect($authorizationUrl);
    }

    public function handleCode()
    {
        if (isset($_GET['code'])) {
            return session([
                'spotify' => [
                    'code' => $_GET['code'],
                ]
            ]);

            return $_GET['code'];
        }

        return session('spotify.code');
    }

    public function handleAccessToken()
    {
        // if(!session('spotify.code')) {
            // $this->requestToken();
        // }

        $accessToken = $this->getAccessToken();

        // if access token has expired, refresh it
        if ($accessToken->hasExpired()) {
            $accessToken = $this->provider->getAccessToken('refresh_token', [
                'refresh_token' => $accessToken->getRefreshToken()
            ]);
        }

        return $accessToken;
    }

    public function getAccessToken()
    {
        try {
            // Try to get an access token using the authorization code grant.
            $accessToken = $this->provider->getAccessToken('authorization_code', [
                'code' => session('spotify.code'),
            ]);

            return $accessToken;
        } catch (IdentityProviderException $e) {
            // Failed to get the access token or user details.
            exit($e->getMessage());
        }
    }

    public function getAlbums()
    {
        $accessToken = $this->handleAccessToken();
        $response = $this->client()->request('GET', 'v1/me/albums', [
            'limit' => 50,
            'headers' => [
                'Authorization' => sprintf('Bearer %s', $accessToken),
            ]
        ]);

        return print_r($this->prase_reponse($response));
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
                'User-Agent' => sprintf('discogslovesspotify/1.0.0 + %s', env('APP_URL')),
                'Content-Type' => 'application/json',
                // 'Authorization' => sprintf('Bearer %s', $this->accessToken()),
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
                'response_type' => 'token',
                'state' => '', // todo
                'scope' => 'user-library-read user-library-modify',
                'show_dialog' => false
            ]
        ];
    }

    /**
     * parse response
     *
     * @param mixed $response
     * @return array
     */
    private function prase_reponse($response)
    {
        if (!$response) {
            return false;
        }
        return json_decode($response->getBody(), true);
    }
}
