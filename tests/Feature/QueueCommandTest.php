<?php

use App\Services\SpotifyService;

test('queue command adds track to queue', function () {
    $searchResult = [
        'uri' => 'spotify:track:123',
        'name' => 'Test Song',
        'artist' => 'Test Artist',
    ];

    $this->mock(SpotifyService::class, function ($mock) use ($searchResult) {
        $mock->shouldReceive('isConfigured')->once()->andReturn(true);
        $mock->shouldReceive('search')
            ->once()
            ->with('test song')
            ->andReturn($searchResult);
        $mock->shouldReceive('addToQueue')
            ->once()
            ->with('spotify:track:123');
    });

    $this->artisan('queue', ['query' => 'test song'])
        ->expectsOutput('🔍 Searching for: test song')
        ->expectsOutput('✅ Added to queue: Test Song by Test Artist')
        ->assertExitCode(0);
});

test('queue command shows current queue when no argument', function () {
    $queueData = [
        'currently_playing' => [
            'name' => 'Current Song',
            'artists' => [['name' => 'Current Artist']],
        ],
        'queue' => [
            ['name' => 'Next Song', 'artists' => [['name' => 'Next Artist']]],
            ['name' => 'Song After', 'artists' => [['name' => 'Another Artist']]],
        ],
    ];

    $this->mock(SpotifyService::class, function ($mock) use ($queueData) {
        $mock->shouldReceive('isConfigured')->once()->andReturn(true);
        $mock->shouldReceive('getQueue')->once()->andReturn($queueData);
    });

    $this->artisan('queue')
        ->expectsOutput('🎵 Currently Playing:')
        ->expectsOutputToContain('Current Song by Current Artist')
        ->expectsOutput('📋 Queue (2 tracks):')
        ->expectsOutputToContain('1. Next Song by Next Artist')
        ->expectsOutputToContain('2. Song After by Another Artist')
        ->assertExitCode(0);
});

test('queue command handles empty queue', function () {
    $queueData = [
        'currently_playing' => null,
        'queue' => [],
    ];

    $this->mock(SpotifyService::class, function ($mock) use ($queueData) {
        $mock->shouldReceive('isConfigured')->once()->andReturn(true);
        $mock->shouldReceive('getQueue')->once()->andReturn($queueData);
    });

    $this->artisan('queue')
        ->expectsOutput('📋 Queue is empty')
        ->assertExitCode(0);
});

test('queue command handles no search results', function () {
    $this->mock(SpotifyService::class, function ($mock) {
        $mock->shouldReceive('isConfigured')->once()->andReturn(true);
        $mock->shouldReceive('search')
            ->once()
            ->with('nonexistent')
            ->andReturn(null);
    });

    $this->artisan('queue', ['query' => 'nonexistent'])
        ->expectsOutput('🔍 Searching for: nonexistent')
        ->expectsOutput('❌ No results found for: nonexistent')
        ->assertExitCode(1);
});

test('queue command handles API errors', function () {
    $searchResult = [
        'uri' => 'spotify:track:123',
        'name' => 'Test Song',
        'artist' => 'Test Artist',
    ];

    $this->mock(SpotifyService::class, function ($mock) use ($searchResult) {
        $mock->shouldReceive('isConfigured')->once()->andReturn(true);
        $mock->shouldReceive('search')
            ->once()
            ->with('test song')
            ->andReturn($searchResult);
        $mock->shouldReceive('addToQueue')
            ->once()
            ->with('spotify:track:123')
            ->andThrow(new Exception('No active device'));
    });

    $this->artisan('queue', ['query' => 'test song'])
        ->expectsOutput('🔍 Searching for: test song')
        ->expectsOutput('❌ Failed to add to queue: No active device')
        ->assertExitCode(1);
});

test('queue command requires configuration', function () {
    $this->mock(SpotifyService::class, function ($mock) {
        $mock->shouldReceive('isConfigured')->once()->andReturn(false);
    });

    $this->artisan('queue')
        ->expectsOutput('❌ Spotify is not configured')
        ->expectsOutput('💡 Run "spotify setup" first')
        ->assertExitCode(1);
});
