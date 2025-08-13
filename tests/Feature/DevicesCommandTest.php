<?php

use App\Services\SpotifyService;

test('devices command lists available devices', function () {
    $devices = [
        [
            'id' => 'device1',
            'name' => 'MacBook Pro',
            'type' => 'Computer',
            'is_active' => true,
            'volume_percent' => 75,
        ],
        [
            'id' => 'device2',
            'name' => 'iPhone',
            'type' => 'Smartphone',
            'is_active' => false,
            'volume_percent' => 50,
        ],
    ];

    $this->mock(SpotifyService::class, function ($mock) use ($devices) {
        $mock->shouldReceive('isConfigured')->once()->andReturn(true);
        $mock->shouldReceive('getDevices')->once()->andReturn($devices);
    });

    $this->artisan('devices')
        ->expectsOutput('📱 Available Spotify devices:')
        ->expectsOutputToContain('🟢 MacBook Pro')
        ->expectsOutputToContain('Computer')
        ->expectsOutputToContain('Volume: 75%')
        ->expectsOutputToContain('⚪ iPhone')
        ->expectsOutputToContain('Smartphone')
        ->expectsOutputToContain('Volume: 50%')
        ->assertExitCode(0);
});

test('devices command handles no devices', function () {
    $this->mock(SpotifyService::class, function ($mock) {
        $mock->shouldReceive('isConfigured')->once()->andReturn(true);
        $mock->shouldReceive('getDevices')->once()->andReturn([]);
    });

    $this->artisan('devices')
        ->expectsOutput('❌ No devices found')
        ->expectsOutput('💡 Open Spotify on any device to see it here')
        ->assertExitCode(0);
});

test('devices command handles API errors', function () {
    $this->mock(SpotifyService::class, function ($mock) {
        $mock->shouldReceive('isConfigured')->once()->andReturn(true);
        $mock->shouldReceive('getDevices')
            ->once()
            ->andThrow(new Exception('API error'));
    });

    $this->artisan('devices')
        ->expectsOutput('❌ Failed to get devices: API error')
        ->assertExitCode(1);
});

test('devices command requires configuration', function () {
    $this->mock(SpotifyService::class, function ($mock) {
        $mock->shouldReceive('isConfigured')->once()->andReturn(false);
    });

    $this->artisan('devices')
        ->expectsOutput('❌ Spotify is not configured')
        ->expectsOutput('💡 Run "spotify setup" first')
        ->assertExitCode(1);
});
