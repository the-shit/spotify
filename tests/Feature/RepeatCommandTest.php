<?php

use App\Services\SpotifyService;

test('repeat command toggles through states', function () {
    $currentPlayback = [
        'name' => 'Test Song',
        'artist' => 'Test Artist',
        'repeat_state' => 'off',
    ];

    $this->mock(SpotifyService::class, function ($mock) use ($currentPlayback) {
        $mock->shouldReceive('isConfigured')->once()->andReturn(true);
        $mock->shouldReceive('getCurrentPlayback')->once()->andReturn($currentPlayback);
        $mock->shouldReceive('setRepeat')->once()->with('context')->andReturn(true);
    });

    $this->artisan('repeat')
        ->expectsOutput('🔁 Repeat current context (album/playlist)')
        ->assertExitCode(0);
});

test('repeat command cycles from context to track', function () {
    $currentPlayback = [
        'name' => 'Test Song',
        'artist' => 'Test Artist',
        'repeat_state' => 'context',
    ];

    $this->mock(SpotifyService::class, function ($mock) use ($currentPlayback) {
        $mock->shouldReceive('isConfigured')->once()->andReturn(true);
        $mock->shouldReceive('getCurrentPlayback')->once()->andReturn($currentPlayback);
        $mock->shouldReceive('setRepeat')->once()->with('track')->andReturn(true);
    });

    $this->artisan('repeat')
        ->expectsOutput('🔂 Repeat current track')
        ->assertExitCode(0);
});

test('repeat command cycles from track to off', function () {
    $currentPlayback = [
        'name' => 'Test Song',
        'artist' => 'Test Artist',
        'repeat_state' => 'track',
    ];

    $this->mock(SpotifyService::class, function ($mock) use ($currentPlayback) {
        $mock->shouldReceive('isConfigured')->once()->andReturn(true);
        $mock->shouldReceive('getCurrentPlayback')->once()->andReturn($currentPlayback);
        $mock->shouldReceive('setRepeat')->once()->with('off')->andReturn(true);
    });

    $this->artisan('repeat')
        ->expectsOutput('➡️  Repeat disabled')
        ->assertExitCode(0);
});

test('repeat command sets specific state', function () {
    $currentPlayback = [
        'name' => 'Test Song',
        'artist' => 'Test Artist',
        'repeat_state' => 'off',
    ];

    $this->mock(SpotifyService::class, function ($mock) use ($currentPlayback) {
        $mock->shouldReceive('isConfigured')->once()->andReturn(true);
        $mock->shouldReceive('getCurrentPlayback')->once()->andReturn($currentPlayback);
        $mock->shouldReceive('setRepeat')->once()->with('track')->andReturn(true);
    });

    $this->artisan('repeat', ['state' => 'track'])
        ->expectsOutput('🔂 Repeat current track')
        ->assertExitCode(0);
});

test('repeat command handles invalid state', function () {
    $currentPlayback = [
        'name' => 'Test Song',
        'artist' => 'Test Artist',
        'repeat_state' => 'off',
    ];

    $this->mock(SpotifyService::class, function ($mock) use ($currentPlayback) {
        $mock->shouldReceive('isConfigured')->once()->andReturn(true);
        $mock->shouldReceive('getCurrentPlayback')->once()->andReturn($currentPlayback);
    });

    $this->artisan('repeat', ['state' => 'invalid'])
        ->expectsOutput("❌ Failed to change repeat mode: Invalid state: invalid. Use 'off', 'track', 'context', or 'toggle'")
        ->assertExitCode(1);
});

test('repeat command requires active playback', function () {
    $this->mock(SpotifyService::class, function ($mock) {
        $mock->shouldReceive('isConfigured')->once()->andReturn(true);
        $mock->shouldReceive('getCurrentPlayback')->once()->andReturn(null);
    });

    $this->artisan('repeat')
        ->expectsOutput('⚠️  Nothing is currently playing')
        ->expectsOutput('💡 Start playing something first')
        ->assertExitCode(1);
});

test('repeat command requires configuration', function () {
    $this->mock(SpotifyService::class, function ($mock) {
        $mock->shouldReceive('isConfigured')->once()->andReturn(false);
    });

    $this->artisan('repeat')
        ->expectsOutput('❌ Spotify is not configured')
        ->expectsOutput('💡 Run "spotify setup" first')
        ->assertExitCode(1);
});

test('repeat command outputs JSON when requested', function () {
    $currentPlayback = [
        'name' => 'Test Song',
        'artist' => 'Test Artist',
        'repeat_state' => 'off',
    ];

    $this->mock(SpotifyService::class, function ($mock) use ($currentPlayback) {
        $mock->shouldReceive('isConfigured')->once()->andReturn(true);
        $mock->shouldReceive('getCurrentPlayback')->once()->andReturn($currentPlayback);
        $mock->shouldReceive('setRepeat')->once()->with('context')->andReturn(true);
    });

    $this->artisan('repeat', ['--json' => true])
        ->expectsOutput('{"repeat":"context","message":"Repeat current context (album\/playlist)"}')
        ->assertExitCode(0);
});
