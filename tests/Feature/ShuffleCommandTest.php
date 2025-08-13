<?php

use App\Services\SpotifyService;

test('shuffle command toggles shuffle state', function () {
    $currentPlayback = [
        'name' => 'Test Song',
        'artist' => 'Test Artist',
        'shuffle_state' => false,
        'repeat_state' => 'off',
    ];

    $this->mock(SpotifyService::class, function ($mock) use ($currentPlayback) {
        $mock->shouldReceive('isConfigured')->once()->andReturn(true);
        $mock->shouldReceive('getCurrentPlayback')->once()->andReturn($currentPlayback);
        $mock->shouldReceive('setShuffle')->once()->with(true)->andReturn(true);
    });

    $this->artisan('shuffle')
        ->expectsOutput('🔀 Shuffle enabled')
        ->assertExitCode(0);
});

test('shuffle command enables shuffle when specified', function () {
    $currentPlayback = [
        'name' => 'Test Song',
        'artist' => 'Test Artist',
        'shuffle_state' => false,
    ];

    $this->mock(SpotifyService::class, function ($mock) use ($currentPlayback) {
        $mock->shouldReceive('isConfigured')->once()->andReturn(true);
        $mock->shouldReceive('getCurrentPlayback')->once()->andReturn($currentPlayback);
        $mock->shouldReceive('setShuffle')->once()->with(true)->andReturn(true);
    });

    $this->artisan('shuffle', ['state' => 'on'])
        ->expectsOutput('🔀 Shuffle enabled')
        ->assertExitCode(0);
});

test('shuffle command disables shuffle when specified', function () {
    $currentPlayback = [
        'name' => 'Test Song',
        'artist' => 'Test Artist',
        'shuffle_state' => true,
    ];

    $this->mock(SpotifyService::class, function ($mock) use ($currentPlayback) {
        $mock->shouldReceive('isConfigured')->once()->andReturn(true);
        $mock->shouldReceive('getCurrentPlayback')->once()->andReturn($currentPlayback);
        $mock->shouldReceive('setShuffle')->once()->with(false)->andReturn(true);
    });

    $this->artisan('shuffle', ['state' => 'off'])
        ->expectsOutput('➡️  Shuffle disabled')
        ->assertExitCode(0);
});

test('shuffle command handles invalid state', function () {
    $currentPlayback = [
        'name' => 'Test Song',
        'artist' => 'Test Artist',
        'shuffle_state' => false,
    ];

    $this->mock(SpotifyService::class, function ($mock) use ($currentPlayback) {
        $mock->shouldReceive('isConfigured')->once()->andReturn(true);
        $mock->shouldReceive('getCurrentPlayback')->once()->andReturn($currentPlayback);
    });

    $this->artisan('shuffle', ['state' => 'invalid'])
        ->expectsOutput("❌ Failed to change shuffle: Invalid state: invalid. Use 'on', 'off', or 'toggle'")
        ->assertExitCode(1);
});

test('shuffle command requires active playback', function () {
    $this->mock(SpotifyService::class, function ($mock) {
        $mock->shouldReceive('isConfigured')->once()->andReturn(true);
        $mock->shouldReceive('getCurrentPlayback')->once()->andReturn(null);
    });

    $this->artisan('shuffle')
        ->expectsOutput('⚠️  Nothing is currently playing')
        ->expectsOutput('💡 Start playing something first')
        ->assertExitCode(1);
});

test('shuffle command requires configuration', function () {
    $this->mock(SpotifyService::class, function ($mock) {
        $mock->shouldReceive('isConfigured')->once()->andReturn(false);
    });

    $this->artisan('shuffle')
        ->expectsOutput('❌ Spotify is not configured')
        ->expectsOutput('💡 Run "spotify setup" first')
        ->assertExitCode(1);
});

test('shuffle command outputs JSON when requested', function () {
    $currentPlayback = [
        'name' => 'Test Song',
        'artist' => 'Test Artist',
        'shuffle_state' => false,
    ];

    $this->mock(SpotifyService::class, function ($mock) use ($currentPlayback) {
        $mock->shouldReceive('isConfigured')->once()->andReturn(true);
        $mock->shouldReceive('getCurrentPlayback')->once()->andReturn($currentPlayback);
        $mock->shouldReceive('setShuffle')->once()->with(true)->andReturn(true);
    });

    $this->artisan('shuffle', ['--json' => true])
        ->expectsOutput('{"shuffle":true,"message":"Shuffle enabled"}')
        ->assertExitCode(0);
});
