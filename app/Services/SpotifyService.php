<?php

namespace Services;

use Session;
use GuzzleHttp\Client;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

class SpotifyService
{
    /**
     * base api uri
     * @var string
     */
    protected $base_api_uri = 'https://api.spotify.com/v1/';

    /**
     * authorize url
     * @var string
     */
    protected $authorize_url = 'https://accounts.spotify.com/authorize';

    /**
     * token url
     * @var string
     */
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

    /**
     * authorise
     * @return redirect
     */
    public function authorise()
    {
        $authorizationUrl = $this->provider->getAuthorizationUrl();
        $authorizationUrl = $authorizationUrl . '&scope=user-library-read%20user-library-modify%20user-follow-modify';

        return redirect($authorizationUrl);
    }

    /**
     * handle code on callback
     * @return string
     */
    public function handleCode()
    {
        if (isset($_GET['code'])) {
            Session::put('spotify_code', $_GET['code']);
            $this->requestToken();
        }

        return Session::get('spotify_code');
    }

    /**
     * handle access token, if expired, refresh it
     * @return string
     */
    public function handleAccessToken()
    {
        $accessToken = Session::get('spotify_access_token');

        // if access token has expired, refresh it
        if ($accessToken->hasExpired()) {
            $accessToken = $this->refreshToken();
        }

        return $accessToken;
    }

    /**
     * request access token
     * @return object
     */
    public function requestToken()
    {
        try {
            // Try to get an access token using the authorization code grant.
            $accessToken = $this->provider->getAccessToken('authorization_code', [
                'code' => Session::get('spotify_code'),
            ]);

            Session::put('spotify_access_token', $accessToken);

            return $accessToken;
        } catch (IdentityProviderException $e) {

        }
    }

    /**
     * refresh access token
     * @return object
     */
    public function refreshToken()
    {
        $accessToken = Session::get('spotify_access_token');

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
        $request = $this->client()->request('GET', 'search', [
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
        $request = $this->client()->request('GET', 'me/albums', [
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
     * get username if available
     * @return mixed
     */
    public function getUserName()
    {
        $accessToken = Session::get('spotify_access_token');
        if(!$accessToken) {
            return false;
        }

        if ($accessToken->hasExpired()) {
            return false;
        }

        $request = $this->client()->request('GET', 'me', [
            'headers' => [
                'Authorization' => sprintf('Bearer %s', $accessToken),
            ]
        ]);

        $response = $this->prase_reponse($request);

        return isset($response['id']) ? $response['id'] : false;
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

    /**
     * save albums to library
     * @param  array $album_chunk
     * @return mixed
     */
    public function saveAlbumsToLibrary($album_chunk)
    {
        $ids = $album_chunk;

        // if array given, make it commma separated sring
        if (is_array($album_chunk)) {
            $ids = implode(',', $album_chunk);
        }

        $accessToken = Session::get('spotify_access_token');

        $request = $this->client()->request('PUT', 'me/albums', [
            'query' => [
                'ids' => $ids,
            ],
            'headers' => [
                'Authorization' => sprintf('Bearer %s', $accessToken),
            ]
        ]);

        if($request->getStatusCode() == 200) {
            return true;
        }

        return false;
    }

    /**
     * follow artists
     * @param  array $artists_chunk
     * @return mixed
     */
    public function followArtists($artists_chunk)
    {
        $ids = $artists_chunk;

        // if array given, make it commma separated sring
        if (is_array($artists_chunk)) {
            $ids = implode(',', $artists_chunk);
        }

        $accessToken = Session::get('spotify_access_token');

        $request = $this->client()->request('PUT', 'me/following', [
            'query' => [
                'type' => 'artist',
                'ids' => $ids,
            ],
            'headers' => [
                'Authorization' => sprintf('Bearer %s', $accessToken),
            ]
        ]);

        if($request->getStatusCode() == 200) {
            return true;
        }

        return false;
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
