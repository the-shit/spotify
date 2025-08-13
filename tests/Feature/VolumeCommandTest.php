<?php

use App\Services\SpotifyService;

test('volume command shows current volume when no argument', function () {
    $currentPlayback = [
        'name' => 'Test Song',
        'artist' => 'Test Artist',
        'device' => [
            'name' => 'Test Device',
            'volume_percent' => 42,
        ],
    ];

    $this->mock(SpotifyService::class, function ($mock) use ($currentPlayback) {
        $mock->shouldReceive('isConfigured')->once()->andReturn(true);
        $mock->shouldReceive('getCurrentPlayback')->once()->andReturn($currentPlayback);
    });

    $this->artisan('volume')
        ->expectsOutput('🎵 Currently playing: Test Song by Test Artist')
        ->expectsOutput('🔊 Current volume on Test Device: 42%')
        ->assertExitCode(0);
});

test('volume command sets volume to specific level', function () {
    $this->mock(SpotifyService::class, function ($mock) {
        $mock->shouldReceive('isConfigured')->once()->andReturn(true);
        $mock->shouldReceive('setVolume')->once()->with(75)->andReturn(true);
    });

    $this->artisan('volume', ['level' => '75'])
        ->expectsOutput('🔊 Volume set to 75%')
        ->assertExitCode(0);
});

test('volume command handles zero volume correctly', function () {
    $this->mock(SpotifyService::class, function ($mock) {
        $mock->shouldReceive('isConfigured')->once()->andReturn(true);
        $mock->shouldReceive('setVolume')->once()->with(0)->andReturn(true);
    });

    $this->artisan('volume', ['level' => '0'])
        ->expectsOutput('🔇 Muted')
        ->assertExitCode(0);
});

test('volume command clamps values above 100', function () {
    $this->mock(SpotifyService::class, function ($mock) {
        $mock->shouldReceive('isConfigured')->once()->andReturn(true);
        $mock->shouldReceive('setVolume')->once()->with(100)->andReturn(true);
    });

    $this->artisan('volume', ['level' => '150'])
        ->expectsOutput('🔊 Volume set to 100% (maximum)')
        ->assertExitCode(0);
});

test('volume command handles negative values', function () {
    $this->mock(SpotifyService::class, function ($mock) {
        $mock->shouldReceive('isConfigured')->once()->andReturn(true);
        $mock->shouldReceive('setVolume')->once()->with(0)->andReturn(true);
    });

    $this->artisan('volume', ['level' => '-10'])
        ->expectsOutput('🔇 Muted')
        ->assertExitCode(0);
});

test('volume command handles API failure', function () {
    $this->mock(SpotifyService::class, function ($mock) {
        $mock->shouldReceive('isConfigured')->once()->andReturn(true);
        $mock->shouldReceive('setVolume')->once()->with(50)->andReturn(false);
    });

    $this->artisan('volume', ['level' => '50'])
        ->expectsOutput('❌ Failed to set volume')
        ->assertExitCode(1);
});

test('volume command requires configuration', function () {
    $this->mock(SpotifyService::class, function ($mock) {
        $mock->shouldReceive('isConfigured')->once()->andReturn(false);
    });

    $this->artisan('volume')
        ->expectsOutput('❌ Spotify is not configured')
        ->expectsOutput('💡 Run "spotify setup" first')
        ->assertExitCode(1);
});

test('volume command handles no active device', function () {
    $currentPlayback = [
        'name' => 'Test Song',
        'artist' => 'Test Artist',
        'device' => null,
    ];

    $this->mock(SpotifyService::class, function ($mock) use ($currentPlayback) {
        $mock->shouldReceive('isConfigured')->once()->andReturn(true);
        $mock->shouldReceive('getCurrentPlayback')->once()->andReturn($currentPlayback);
    });

    $this->artisan('volume')
        ->expectsOutput('❌ No active device found')
        ->assertExitCode(1);
});
