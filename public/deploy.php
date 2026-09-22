<?php
// Secret security key
$secret = 'MySecretKey123!';

// Validate secret key from URL query string
if (!isset($_GET['key']) || $_GET['key'] !== $secret) {
    http_response_code(403);
    die('Unauthorized access');
}

// Ensure the request method is POST or GET
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method !== 'POST' && $method !== 'GET') {
    http_response_code(405);
    die('Method Not Allowed');
}

// Determine project base directory
$projectDir = is_dir('/home/nexviabackend') ? '/home/nexviabackend' : dirname(__DIR__);

// Prevent "fatal: detected dubious ownership" in git when running via web server user
@exec('git config --global --add safe.directory ' . escapeshellarg($projectDir));
@exec('git config --global --add safe.directory "*"');

// Detect PHP binary
$phpBin = defined('PHP_BINARY') && is_executable(PHP_BINARY) ? PHP_BINARY : 'php';

// Execute deployment commands
$command = "cd " . escapeshellarg($projectDir) . " && git pull origin main 2>&1 && {$phpBin} artisan migrate --force 2>&1 && {$phpBin} artisan config:clear 2>&1 && {$phpBin} artisan cache:clear 2>&1";
$output = shell_exec($command);

http_response_code(200);
echo "<pre>Deployment Triggered ($method):\n$output</pre>";
