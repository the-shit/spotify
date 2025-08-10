<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SpotifyService
{
    private ?string $accessToken = null;
    private string $clientId;
    private string $clientSecret;
    private string $baseUri = 'https://api.spotify.com/v1/';

    public function __construct()
    {
        $this->clientId = config('spotify.client_id', '');
        $this->clientSecret = config('spotify.client_secret', '');
        $this->loadAccessToken();
    }

    /**
     * Check if Spotify is properly configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->clientId) && !empty($this->clientSecret) && !empty($this->accessToken);
    }
    
    /**
     * Load access token from storage
     */
    private function loadAccessToken(): void
    {
        $tokenFile = $_SERVER['HOME'] . '/.spotify_token';
        if (file_exists($tokenFile)) {
            $this->accessToken = trim(file_get_contents($tokenFile));
        }
    }
    
    /**
     * Save access token to storage
     */
    private function saveAccessToken(string $token): void
    {
        $tokenFile = $_SERVER['HOME'] . '/.spotify_token';
        file_put_contents($tokenFile, $token);
        $this->accessToken = $token;
    }
    
    /**
     * Search for tracks on Spotify
     */
    public function search(string $query, string $type = 'track'): ?array
    {
        if (!$this->accessToken) {
            throw new \Exception('Not authenticated. Run "spotify:login" first.');
        }
        
        $response = Http::withToken($this->accessToken)
            ->get($this->baseUri . 'search', [
                'q' => $query,
                'type' => $type,
                'limit' => 1
            ]);
            
        if ($response->successful()) {
            $data = $response->json();
            if (isset($data['tracks']['items'][0])) {
                $track = $data['tracks']['items'][0];
                return [
                    'uri' => $track['uri'],
                    'name' => $track['name'],
                    'artist' => $track['artists'][0]['name'] ?? 'Unknown'
                ];
            }
        }
        
        return null;
    }
    
    /**
     * Play a track by URI
     */
    public function play(string $uri): void
    {
        if (!$this->accessToken) {
            throw new \Exception('Not authenticated. Run "spotify:login" first.');
        }
        
        Http::withToken($this->accessToken)
            ->put($this->baseUri . 'me/player/play', [
                'uris' => [$uri]
            ]);
    }
    
    /**
     * Resume playback
     */
    public function resume(): void
    {
        if (!$this->accessToken) {
            throw new \Exception('Not authenticated. Run "spotify:login" first.');
        }
        
        Http::withToken($this->accessToken)
            ->put($this->baseUri . 'me/player/play');
    }
    
    /**
     * Pause playback
     */
    public function pause(): void
    {
        if (!$this->accessToken) {
            throw new \Exception('Not authenticated. Run "spotify:login" first.');
        }
        
        Http::withToken($this->accessToken)
            ->put($this->baseUri . 'me/player/pause');
    }
    
    /**
     * Skip to next track
     */
    public function next(): void
    {
        if (!$this->accessToken) {
            throw new \Exception('Not authenticated. Run "spotify:login" first.');
        }
        
        Http::withToken($this->accessToken)
            ->post($this->baseUri . 'me/player/next');
    }
    
    /**
     * Skip to previous track
     */
    public function previous(): void
    {
        if (!$this->accessToken) {
            throw new \Exception('Not authenticated. Run "spotify:login" first.');
        }
        
        Http::withToken($this->accessToken)
            ->post($this->baseUri . 'me/player/previous');
    }
    
    /**
     * Get current playback state
     */
    public function getCurrentPlayback(): ?array
    {
        if (!$this->accessToken) {
            return null;
        }
        
        $response = Http::withToken($this->accessToken)
            ->get($this->baseUri . 'me/player/currently-playing');
            
        if ($response->successful()) {
            $data = $response->json();
            if (isset($data['item'])) {
                return [
                    'name' => $data['item']['name'],
                    'artist' => $data['item']['artists'][0]['name'] ?? 'Unknown',
                    'album' => $data['item']['album']['name'] ?? 'Unknown',
                    'progress_ms' => $data['progress_ms'] ?? 0,
                    'duration_ms' => $data['item']['duration_ms'] ?? 0,
                    'is_playing' => $data['is_playing'] ?? false
                ];
            }
        }
        
        return null;
    }
}