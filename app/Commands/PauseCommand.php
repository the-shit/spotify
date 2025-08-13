<?php

namespace App\Commands;

use App\Services\SpotifyService;
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
        $spotify = new SpotifyService;

        if (! $spotify->isConfigured()) {
            $this->error('❌ Spotify is not configured');
            $this->info('💡 Set SPOTIFY_CLIENT_ID and SPOTIFY_CLIENT_SECRET env vars');
            $this->info('💡 Then run "spotify:login" to authenticate');

            return self::FAILURE;
        }

        $this->info('⏸️  Pausing Spotify playback...');

        try {
            // Get current track before pausing
            $current = $spotify->getCurrentPlayback();

            $spotify->pause();
            $this->info('✅ Playback paused!');

            // Emit pause event
            if ($current) {
                $this->call('event:emit', [
                    'event' => 'track.paused',
                    'data' => json_encode([
                        'track' => $current['name'],
                        'artist' => $current['artist'],
                        'paused_at' => $current['progress_ms'],
                    ]),
                ]);
            }
        } catch (\Exception $e) {
            $this->error('❌ Failed to pause: '.$e->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
