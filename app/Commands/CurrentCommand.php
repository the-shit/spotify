<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;

class CurrentCommand extends Command
{
    protected $signature = 'current';
    
    protected $description = 'Show current track';

    public function handle()
    {
        $this->info("🎵 Currently Playing:");
        $this->newLine();
        
        // Mock data for now - would come from Spotify API
        $track = [
            'name' => 'Never Gonna Give You Up',
            'artist' => 'Rick Astley',
            'album' => 'Whenever You Need Somebody',
            'progress' => '1:42',
            'duration' => '3:33',
        ];
        
        $this->line("  <fg=cyan>Track:</> {$track['name']}");
        $this->line("  <fg=cyan>Artist:</> {$track['artist']}");
        $this->line("  <fg=cyan>Album:</> {$track['album']}");
        $this->line("  <fg=cyan>Progress:</> {$track['progress']} / {$track['duration']}");
        
        // Progress bar
        $progress = 50; // Mock 50% progress
        $bar = str_repeat('▓', (int)($progress / 5)) . str_repeat('░', 20 - (int)($progress / 5));
        $this->line("  <fg=cyan>[$bar]</> {$progress}%");
        
        return self::SUCCESS;
    }
}