<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;

class PauseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pause';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pause Spotify playback';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $spotify = new \App\Services\SpotifyService();
        
        if (!$spotify->isConfigured()) {
            $this->error('❌ Spotify is not configured');
            $this->info('💡 Set SPOTIFY_CLIENT_ID and SPOTIFY_CLIENT_SECRET env vars');
            $this->info('💡 Then run "spotify:login" to authenticate');
            return self::FAILURE;
        }
        
        $this->info('⏸️  Pausing Spotify playback...');
        
        try {
            $spotify->pause();
            $this->info('✅ Playback paused!');
        } catch (\Exception $e) {
            $this->error('❌ Failed to pause: ' . $e->getMessage());
            return self::FAILURE;
        }
        
        return self::SUCCESS;
    }
}
