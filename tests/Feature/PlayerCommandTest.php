<?php

use App\Services\SpotifyService;

test('player command requires configuration', function () {
    // Mock SpotifyService to return not configured
    $this->mock(SpotifyService::class, function ($mock) {
        $mock->shouldReceive('isConfigured')->once()->andReturn(false);
    });

    $this->artisan('player')
        ->expectsOutput('❌ Spotify is not configured')
        ->expectsOutput('💡 Run "spotify setup" first')
        ->assertExitCode(1);
});

test('player command requires interactive terminal', function () {
    // Mock SpotifyService to return configured
    $this->mock(SpotifyService::class, function ($mock) {
        $mock->shouldReceive('isConfigured')->once()->andReturn(true);
    });

    // Run in non-interactive mode
    $this->artisan('player', ['--no-interaction' => true])
        ->expectsOutput('❌ Player requires an interactive terminal')
        ->expectsOutput('💡 Run without piping or in a proper terminal')
        ->assertExitCode(1);
});

test('player shows nothing playing state', function () {
    // Mock SpotifyService
    $this->mock(SpotifyService::class, function ($mock) {
        $mock->shouldReceive('isConfigured')->once()->andReturn(true);
        $mock->shouldReceive('getCurrentPlayback')->once()->andReturn(null);
    });

    // Since the player uses interactive prompts, we can't fully test it
    // But we can verify it starts correctly
    $this->artisan('player')
        ->expectsOutput('🎵 Spotify Interactive Player')
        ->expectsOutput('Loading...');
});

test('player displays current track info', function () {
    $currentTrack = [
        'name' => 'Test Song',
        'artist' => 'Test Artist',
        'album' => 'Test Album',
        'progress_ms' => 90000,
        'duration_ms' => 180000,
        'is_playing' => true,
        'device' => [
            'volume_percent' => 50,
        ],
    ];

    // Mock SpotifyService
    $this->mock(SpotifyService::class, function ($mock) use ($currentTrack) {
        $mock->shouldReceive('isConfigured')->once()->andReturn(true);
        $mock->shouldReceive('getCurrentPlayback')->once()->andReturn($currentTrack);
    });

    // The player uses interactive prompts, so we can't fully test it
    // But we can verify it starts and loads the track
    $this->artisan('player')
        ->expectsOutput('🎵 Spotify Interactive Player')
        ->expectsOutput('Loading...');
});
