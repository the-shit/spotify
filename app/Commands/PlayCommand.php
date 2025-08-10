<?php

namespace App\Commands;

use App\Services\SpotifyService;
use LaravelZero\Framework\Commands\Command;

class PlayCommand extends Command
{
    protected $signature = 'play {query? : Song, artist, or playlist to play}';
    
    protected $description = 'Start Spotify playback';

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
        
        if ($query) {
            $this->info("🎵 Searching for: {$query}");
            
            try {
                $result = $spotify->search($query);
                
                if ($result) {
                    $this->info("▶️  Playing: {$result['name']} by {$result['artist']}");
                    $spotify->play($result['uri']);
                } else {
                    $this->warn("No results found for: {$query}");
                    return self::FAILURE;
                }
            } catch (\Exception $e) {
                $this->error("Failed to play: " . $e->getMessage());
                return self::FAILURE;
            }
        } else {
            $this->info("▶️  Resuming Spotify playback...");
            try {
                $spotify->resume();
            } catch (\Exception $e) {
                $this->error("Failed to resume: " . $e->getMessage());
                return self::FAILURE;
            }
        }
        
        $this->info("✅ Playback started!");
        
        return self::SUCCESS;
    }
}