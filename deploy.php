<?php
// Secret security key to prevent unauthorized access
$secret = 'MySecretKey123!';

// Check if the request contains the correct secret key
if (!isset($_GET['key']) || $_GET['key'] !== $secret) {
    http_response_code(403);
    die('Unauthorized access');
}

// Execute git pull, run pending migrations, and clear Laravel caches
$output = shell_exec('cd /home/nexviabackend && git pull origin main 2>&1 && php artisan migrate --force 2>&1 && php artisan config:clear 2>&1 && php artisan cache:clear 2>&1');

echo "<pre>$output</pre>";