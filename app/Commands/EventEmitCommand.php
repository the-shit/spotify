<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;

class EventEmitCommand extends Command
{
    protected $signature = 'event:emit {event : Event name} {data? : JSON data}';

    protected $description = 'Emit an event to THE SHIT event bus';

    public function handle()
    {
        $event = $this->argument('event');
        $data = $this->argument('data') ? json_decode($this->argument('data'), true) : [];

        // Simple file-based event queue
        $eventData = [
            'component' => 'spotify',
            'event' => "spotify.{$event}",
            'data' => $data,
            'timestamp' => now()->toIso8601String(),
        ];

        // Navigate from component to THE SHIT root
        // We're in: the-shit/💩-components/spotify/
        // We need: the-shit/storage/events.jsonl
        $queueFile = dirname(dirname(base_path())).'/storage/events.jsonl';

        // Ensure directory exists
        $dir = dirname($queueFile);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // Append event to queue
        file_put_contents(
            $queueFile,
            json_encode($eventData)."\n",
            FILE_APPEND | LOCK_EX
        );

        $this->info("✅ Event emitted: spotify.{$event}");

        return self::SUCCESS;
    }
}
