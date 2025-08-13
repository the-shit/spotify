<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Spotify API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for THE SHIT Spotify integration
    |
    */

    'client_id' => env('SPOTIFY_CLIENT_ID'),
    'client_secret' => env('SPOTIFY_CLIENT_SECRET'),

    'redirect_uri' => env('SPOTIFY_REDIRECT_URI', 'http://127.0.0.1:8888/callback'),

    'scopes' => [
        'user-read-playback-state',
        'user-modify-playback-state',
        'user-read-currently-playing',
        'streaming',
        'playlist-read-private',
        'playlist-read-collaborative',
    ],

    'token_path' => env('SPOTIFY_TOKEN_PATH', $_SERVER['HOME'].'/.spotify_token'),
];
