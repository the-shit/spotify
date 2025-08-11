<?php

namespace App\Commands;

use App\Services\SpotifyService;
use LaravelZero\Framework\Commands\Command;

class ResumeCommand extends Command
{
    protected $signature = 'resume {--device= : Device name or ID to resume on}';
    
    protected $description = 'Resume Spotify playback from where it was paused';

    public function handle()
    {
        $spotify = new SpotifyService();
        
        if (!$spotify->isConfigured()) {
            $this->error('❌ Spotify is not configured');
            $this->info('💡 Run "spotify setup" to configure Spotify');
            $this->info('💡 Or set SPOTIFY_CLIENT_ID and SPOTIFY_CLIENT_SECRET env vars');
            return self::FAILURE;
        }
        
        $deviceName = $this->option('device');
        
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
        
        $this->info("▶️  Resuming Spotify playback...");
        
        try {
            // If device specified, transfer playback first
            if ($deviceId) {
                $spotify->transferPlayback($deviceId, true);
            } else {
                $spotify->resume();
            }
            
            // Get current track info for event
            $current = $spotify->getCurrentPlayback();
            
            // Emit resume event
            $this->call('event:emit', [
                'event' => 'track.resumed',
                'data' => json_encode([
                    'track' => $current['name'] ?? null,
                    'artist' => $current['artist'] ?? null,
                    'device_id' => $deviceId
                ])
            ]);
            
            if ($current) {
                $this->info("🎵 Resumed: {$current['name']} by {$current['artist']}");
            }
            
            $this->info("✅ Playback resumed!");
            
        } catch (\Exception $e) {
            $this->error("Failed to resume: " . $e->getMessage());
            
            // Emit error event
            $this->call('event:emit', [
                'event' => 'error.playback_failed',
                'data' => json_encode([
                    'command' => 'resume',
                    'action' => 'resume',
                    'error' => $e->getMessage()
                ])
            ]);
            
            return self::FAILURE;
        }
        
        return self::SUCCESS;
    }
}