<?php
// Secret security key
$secret = 'MySecretKey123!';

// Validate secret key from URL query string
if (!isset($_GET['key']) || $_GET['key'] !== $secret) {
    http_response_code(403);
    die('Unauthorized access');
}

// Ensure the request method is POST or GET
$method = $_SERVER['REQUEST_METHOD'];
if ($method !== 'POST' && $method !== 'GET') {
    http_response_code(405);
    die('Method Not Allowed');
}

// Execute deployment commands
$command = 'cd /home/nexviabackend && git pull origin main 2>&1 && php artisan migrate --force 2>&1 && php artisan config:clear 2>&1 && php artisan cache:clear 2>&1';
$output = shell_exec($command);

http_response_code(200);
echo "<pre>Deployment Triggered ($method):\n$output</pre>";