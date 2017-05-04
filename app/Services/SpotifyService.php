<?php

namespace Services;

use GuzzleHttp\Client;
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
        $this->callback_url = env('SPOTIFY_CALLBACK_URL');

        $this->provider = new GenericProvider([
            'clientId'                => $this->client_id,    // The client ID assigned to you by the provider
            'clientSecret'            => $this->client_secret,   // The client password assigned to you by the provider
            'redirectUri'             => $this->callback_url,
            'urlAuthorize'            => $this->authorize_url,
            'urlAccessToken'          => $this->token_url,
            'urlResourceOwnerDetails' => '',
        ]);
    }

    public function authorise()
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
        $accessToken = $this->requestToken();

        // if access token has expired, refresh it
        if ($accessToken->hasExpired()) {
            $accessToken = $this->refreshToken();
        }

        return $accessToken;
    }

    public function requestToken()
    {
        try {
            // Try to get an access token using the authorization code grant.
            $accessToken = $this->provider->getAccessToken('authorization_code', [
                'code' => session('spotify.code'),
            ]);

            return $accessToken;
        } catch (IdentityProviderException $e) {
            // Failed to get the access token or user details.
            dd($e);
        }
    }

    public function refreshToken()
    {
        $accessToken = $this->requestToken();

        return $this->provider->getAccessToken('refresh_token', [
            'refresh_token' => $accessToken->getRefreshToken()
        ]);
    }

    /**
     * search albums
     * @param  array $album
     * @return array
     */
    public function searchAlbums($album)
    {
        $query = sprintf('album:%s+artist:%s', $album['title'], $album['artist']);

        // search q
        $request = $this->client()->request('GET', 'v1/search', [
            'query' => [
                'q' => $this->formatQuery($query),
                'type' => 'album'
            ],
        ]);

        // parse
        $response = $this->prase_reponse($request);

        // check if we have items and return
        $items = count($response['albums']['items']) ? $response['albums']['items'] : [];

        // filter to get albums only (not singles) which is often the case that
        // single carries same name as album. E.g. Jack White - Lazaretto
        return $this->filterAlbumsOnly($items);
    }

    /**
     * get albums
     * @return array
     */
    public function getAlbums()
    {
        $accessToken = $this->handleAccessToken();
        $request = $this->client()->request('GET', 'v1/me/albums', [
            'query' => [
              'limit' => 50,
            ],
            'headers' => [
                'Authorization' => sprintf('Bearer %s', $accessToken),
            ]
        ]);

        $response = $this->prase_reponse($request);
        return isset($response['items']) ? $response['items'] : $response;
    }

    /**
     * get spotify album and artist ids from (discogs) albums
     * @param  array  $albums
     * @return array
     */
    public function getAlbumAndArtistIds($albums = [])
    {
        if (!$albums) {
            return false;
        }

        $spotify_ids = [];

        foreach ($albums as $album) {
            $search = $this->searchAlbums($album);

            if (isset($search[0])) {
                $spotify_ids['albums'][] = $search[0]['id'];
                $spotify_ids['artists'][] = $search[0]['artists'][0]['id'];
            }

            // play nicely with rate limitation
            sleep(1);
        }

        return $spotify_ids;
    }

    public function saveAlbumsToLibrary($album_chunk)
    {
        $ids = $album_chunk;

        // if array given, make it commma separated sring
        if (is_array($album_chunk)) {
            $ids = implode(',', $album_chunk);
        }

        $accessToken = $this->handleAccessToken();

        $request = $this->client()->request('PUT', 'v1/me/albums', [
            'query' => [
                'ids' => $ids,
            ],
            'headers' => [
                'Authorization' => sprintf('Bearer %s', $accessToken),
            ]
        ]);

        $response = $this->prase_reponse($request);

        return $response;
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

    /**
     * filter to get albums only
     * @param  array $records
     * @return array
     */
    private function filterAlbumsOnly($records)
    {
        $type = 'album';
        $items = array_filter($records, function ($var) use ($type) {
            return ($var['album_type'] == $type);
        });

        return $items;
    }

    /**
     * format query
     * @param  string $query
     * @return string
     */
    private function formatQuery($query)
    {
        // remove some random discogs additions
        $query = str_replace(' (1)', '', $query);
        $query = str_replace(' (2)', '', $query);

        // to make sure we don't double "urlencode" which will cause in invalid
        // query
        return urldecode($query);
    }
}
