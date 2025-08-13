<?php

namespace App\Commands;

use App\Services\SpotifyService;
use LaravelZero\Framework\Commands\Command;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;

class SkipCommand extends Command
{
    protected $signature = 'skip {direction? : next or prev (default: next)}';

    protected $description = 'Skip to next or previous track';

    public function handle(SpotifyService $spotify)
    {
        if (! $spotify->isConfigured()) {
            error('❌ Spotify not configured');
            info('💡 Run "spotify setup" to get started');

            return self::FAILURE;
        }

        $direction = $this->argument('direction') ?? 'next';

        try {
            // Get current track before skipping
            $before = $spotify->getCurrentPlayback();

            if ($direction === 'prev' || $direction === 'previous') {
                $spotify->previous();
                info('⏮️  Skipped to previous track');
            } else {
                $spotify->next();
                info('⏭️  Skipped to next track');
            }

            // Show what's playing now
            sleep(1); // Give Spotify a moment to update
            $current = $spotify->getCurrentPlayback();
            if ($current) {
                info("🎵 Now playing: {$current['name']} by {$current['artist']}");
            }

            // Emit skip event
            if ($before) {
                $this->call('event:emit', [
                    'event' => 'track.skipped',
                    'data' => json_encode([
                        'track' => $before['name'],
                        'artist' => $before['artist'],
                        'skip_at' => $before['progress_ms'],
                        'direction' => $direction === 'prev' ? 'previous' : 'next',
                    ]),
                ]);
            }

            return self::SUCCESS;
        } catch (\Exception $e) {
            error('❌ '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
