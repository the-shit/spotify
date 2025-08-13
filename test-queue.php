#!/usr/bin/env php
<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

// Create service and get queue
$spotify = new \App\Services\SpotifyService;

if (! $spotify->isConfigured()) {
    echo "Not configured\n";
    exit(1);
}

$queue = $spotify->getQueue();

echo "🎵 Currently Playing:\n";
if ($queue['currently_playing']) {
    $current = $queue['currently_playing'];
    echo '   '.$current['name'].' by '.($current['artists'][0]['name'] ?? 'Unknown')."\n\n";
}

echo '📋 Queue ('.count($queue['queue'])." tracks):\n";
foreach (array_slice($queue['queue'], 0, 5) as $i => $track) {
    echo ($i + 1).'. '.$track['name'].' by '.($track['artists'][0]['name'] ?? 'Unknown')."\n";
}

if (count($queue['queue']) > 5) {
    echo '... and '.(count($queue['queue']) - 5)." more\n";
}
