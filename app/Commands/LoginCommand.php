<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Http;
use function Laravel\Prompts\info;
use function Laravel\Prompts\warning;
use function Laravel\Prompts\error;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\confirm;

class LoginCommand extends Command
{
    protected $signature = 'login';
    
    protected $description = 'Authenticate with Spotify';

    public function handle()
    {
        $clientId = config('spotify.client_id');
        $clientSecret = config('spotify.client_secret');
        
        if (!$clientId || !$clientSecret) {
            error('❌ Missing Spotify credentials');
            info('💡 Run "spotify setup" to get started');
            return self::FAILURE;
        }
        
        info('🎵 Spotify Authentication');
        
        // Find available port
        $port = $this->findAvailablePort();
        $redirectUri = "http://127.0.0.1:{$port}/callback";
        
        if ($port !== 8888) {
            warning("⚠️  Using port {$port} because 8888 is in use");
            warning("⚠️  You must add this EXACT URI to your Spotify app:");
            warning("   {$redirectUri}");
            info('');
            info('Or kill the process using port 8888:');
            info('   lsof -ti:8888 | xargs kill -9');
            
            if (!confirm('Continue with port ' . $port . '?')) {
                return self::FAILURE;
            }
        }
        
        info("📋 Using redirect URI: {$redirectUri}");
        
        // Generate auth URL
        $scopes = [
            'user-read-playback-state',
            'user-modify-playback-state', 
            'user-read-currently-playing',
            'streaming',
            'playlist-read-private',
            'playlist-read-collaborative'
        ];
        
        $state = bin2hex(random_bytes(16));
        $authUrl = 'https://accounts.spotify.com/authorize?' . http_build_query([
            'client_id' => $clientId,
            'response_type' => 'code',
            'redirect_uri' => $redirectUri,
            'scope' => implode(' ', $scopes),
            'state' => $state
        ]);
        
        // Start local server
        $serverScript = $this->createCallbackServer();
        $serverProcess = Process::timeout(60)->start("php -S 127.0.0.1:{$port} {$serverScript}");
        
        sleep(1); // Give server time to start
        
        // Open browser
        info('🌐 Opening browser for authorization...');
        $this->openUrl($authUrl);
        
        // Wait for callback with spinner
        $code = spin(
            fn() => $this->waitForAuthCode(),
            'Waiting for authorization...'
        );
        
        $serverProcess->stop();
        
        if (!$code) {
            error('❌ Authorization timeout or cancelled');
            return self::FAILURE;
        }
        
        info('🔐 Got authorization code!');
        
        // Exchange code for token
        $token = spin(
            fn() => $this->exchangeCodeForToken($clientId, $clientSecret, $code, $redirectUri),
            'Exchanging code for access token...'
        );
        
        if (!$token) {
            error('❌ Failed to get access token');
            return self::FAILURE;
        }
        
        // Save token
        $tokenFile = $_SERVER['HOME'] . '/.spotify_token';
        file_put_contents($tokenFile, $token);
        
        info('✅ Successfully authenticated with Spotify!');
        info('');
        info('Try these commands:');
        info('  ./💩 spotify:play "Never Gonna Give You Up"');
        info('  ./💩 spotify:pause');
        info('  ./💩 spotify:current');
        
        return self::SUCCESS;
    }
    
    private function findAvailablePort(): int
    {
        $ports = [8888, 8889, 8890, 8891, 8892];
        
        foreach ($ports as $port) {
            $connection = @fsockopen('127.0.0.1', $port, $errno, $errstr, 1);
            if (!$connection) {
                return $port;
            }
            fclose($connection);
        }
        
        return 8888; // Fallback
    }
    
    private function createCallbackServer(): string
    {
        $serverScript = sys_get_temp_dir() . '/spotify_callback.php';
        $codeFile = sys_get_temp_dir() . '/spotify_code.txt';
        
        file_put_contents($serverScript, '<?php
$uri = $_SERVER["REQUEST_URI"];
if (str_starts_with($uri, "/callback") && isset($_GET["code"])) {
    file_put_contents("' . $codeFile . '", $_GET["code"]);
    echo "<!DOCTYPE html>
<html>
<head>
    <title>THE SHIT - Spotify Connected</title>
    <style>
        body {
            background: #000;
            color: #1DB954;
            font-family: -apple-system, system-ui, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            text-align: center;
        }
        h1 {
            font-size: 4em;
            margin: 0;
        }
        p {
            font-size: 1.5em;
            color: #fff;
        }
    </style>
</head>
<body>
    <div class=\"container\">
        <h1>💩 ✅</h1>
        <p>THE SHIT is connected to Spotify!</p>
        <p style=\"font-size: 1em; color: #666;\">You can close this window</p>
    </div>
</body>
</html>";
    exit;
}
echo "Waiting for Spotify callback...";
');
        
        return $serverScript;
    }
    
    private function waitForAuthCode(): ?string
    {
        $codeFile = sys_get_temp_dir() . '/spotify_code.txt';
        $timeout = 60;
        $start = time();
        
        // Clear old code file if exists
        if (file_exists($codeFile)) {
            unlink($codeFile);
        }
        
        while ((time() - $start) < $timeout) {
            if (file_exists($codeFile)) {
                $code = trim(file_get_contents($codeFile));
                unlink($codeFile);
                return $code;
            }
            usleep(100000); // 100ms
        }
        
        return null;
    }
    
    private function exchangeCodeForToken(string $clientId, string $clientSecret, string $code, string $redirectUri): ?string
    {
        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . base64_encode($clientId . ':' . $clientSecret),
        ])->asForm()->post('https://accounts.spotify.com/api/token', [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $redirectUri
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return $data['access_token'] ?? null;
        }
        
        return null;
    }
    
    private function openUrl(string $url): void
    {
        $os = PHP_OS_FAMILY;
        
        $command = match($os) {
            'Darwin' => "open '{$url}'",
            'Linux' => "xdg-open '{$url}'",
            'Windows' => "start {$url}",
            default => null
        };
        
        if ($command) {
            Process::run($command);
        }
    }
}