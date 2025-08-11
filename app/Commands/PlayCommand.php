<?php

namespace App\Commands;

use App\Services\SpotifyService;
use LaravelZero\Framework\Commands\Command;

class PlayCommand extends Command
{
    protected $signature = 'play {query : Song, artist, or playlist to play} {--device= : Device name or ID to play on} {--queue : Add to queue instead of playing immediately}';
    
    protected $description = 'Play a specific song/artist/playlist (use "resume" to continue paused playback)';

    public function handle()
    {
        $spotify = new SpotifyService();
        
        if (!$spotify->isConfigured()) {
            $this->error('❌ Spotify is not configured');
            $this->info('💡 Run "spotify setup" to configure Spotify');
            $this->info('💡 Or set SPOTIFY_CLIENT_ID and SPOTIFY_CLIENT_SECRET env vars');
            return self::FAILURE;
        }
        
        $query = $this->argument('query');
        $deviceName = $this->option('device');
        
        if (!$query) {
            $this->error('❌ Please specify what to play');
            $this->info('💡 Usage: spotify play "song name"');
            $this->info('💡 Or use "spotify resume" to continue paused playback');
            return self::FAILURE;
        }
        
        // Find device by name if specified
        $deviceId = null;
        if ($deviceName) {
            $devices = $spotify->getDevices();
            foreach ($devices as $device) {
                if (stripos($device['name'], $deviceName) !== false || $device['id'] === $deviceName) {
                    $deviceId = $device['id'];
                    $this->info("🔊 Using device: {$device['name']}");
                    break;
                }
            }
            
            if (!$deviceId) {
                $this->error("❌ Device '{$deviceName}' not found");
                return self::FAILURE;
            }
        }
        
        $this->info("🎵 Searching for: {$query}");
            
        try {
            $result = $spotify->search($query);
            
            if ($result) {
                if ($this->option('queue')) {
                    // Add to queue instead of playing
                    $spotify->addToQueue($result['uri']);
                    $this->info("➕ Added to queue: {$result['name']} by {$result['artist']}");
                    $this->info("📋 It will play after the current track");
                    
                    // Emit queue event
                    $this->call('event:emit', [
                        'event' => 'track.queued',
                        'data' => json_encode([
                            'track' => $result['name'],
                            'artist' => $result['artist'],
                            'uri' => $result['uri'],
                            'search_query' => $query
                        ])
                    ]);
                } else {
                    // Play immediately
                    $this->info("▶️  Playing: {$result['name']} by {$result['artist']}");
                    $spotify->play($result['uri'], $deviceId);
                    
                    // Emit play event
                    $this->call('event:emit', [
                        'event' => 'track.played',
                        'data' => json_encode([
                            'track' => $result['name'],
                            'artist' => $result['artist'],
                            'uri' => $result['uri'],
                            'search_query' => $query
                        ])
                    ]);
                }
            } else {
                $this->warn("No results found for: {$query}");
                return self::FAILURE;
            }
        } catch (\Exception $e) {
            $this->error("Failed to play: " . $e->getMessage());
            
            // Emit error event
            $this->call('event:emit', [
                'event' => 'error.playback_failed',
                'data' => json_encode([
                    'command' => 'play',
                    'action' => 'play',
                    'error' => $e->getMessage()
                ])
            ]);
            
            return self::FAILURE;
        }
        
        $this->info("✅ Playback started!");
        
        return self::SUCCESS;
    }
}