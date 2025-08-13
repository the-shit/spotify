<?php

use App\Services\SpotifyService;

test('play command searches and plays a track', function () {
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
        $mock->shouldReceive('play')
            ->once()
            ->with('spotify:track:123');
    });

    $this->artisan('play', ['query' => 'test song'])
        ->expectsOutput('🔍 Searching for: test song')
        ->expectsOutput('▶️  Playing: Test Song by Test Artist')
        ->assertExitCode(0);
});

test('play command handles no search results', function () {
    $this->mock(SpotifyService::class, function ($mock) {
        $mock->shouldReceive('isConfigured')->once()->andReturn(true);
        $mock->shouldReceive('search')
            ->once()
            ->with('nonexistent song')
            ->andReturn(null);
    });

    $this->artisan('play', ['query' => 'nonexistent song'])
        ->expectsOutput('🔍 Searching for: nonexistent song')
        ->expectsOutput('❌ No results found for: nonexistent song')
        ->assertExitCode(1);
});

test('play command requires query argument', function () {
    $this->mock(SpotifyService::class, function ($mock) {
        $mock->shouldReceive('isConfigured')->once()->andReturn(true);
    });

    $this->artisan('play')
        ->expectsOutput('❌ Please provide a search query')
        ->expectsOutput('💡 Usage: spotify play "song name"')
        ->assertExitCode(1);
});

test('play command handles API errors gracefully', function () {
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
        $mock->shouldReceive('play')
            ->once()
            ->with('spotify:track:123')
            ->andThrow(new Exception('No active device'));
    });

    $this->artisan('play', ['query' => 'test song'])
        ->expectsOutput('🔍 Searching for: test song')
        ->expectsOutput('❌ Failed to play: No active device')
        ->assertExitCode(1);
});

test('play command requires configuration', function () {
    $this->mock(SpotifyService::class, function ($mock) {
        $mock->shouldReceive('isConfigured')->once()->andReturn(false);
    });

    $this->artisan('play', ['query' => 'test'])
        ->expectsOutput('❌ Spotify is not configured')
        ->expectsOutput('💡 Run "spotify setup" first')
        ->assertExitCode(1);
});
