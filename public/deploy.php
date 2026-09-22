<?php
// Display errors for debugging deployment issues
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

header('Content-Type: text/plain; charset=utf-8');

try {
    // Secret security key
    $secret = 'MySecretKey123!';

    // Validate secret key from URL query string or headers
    $providedKey = $_GET['key'] ?? $_POST['key'] ?? ($_SERVER['HTTP_X_DEPLOY_KEY'] ?? null);
    if ($providedKey !== $secret) {
        http_response_code(403);
        die("403 Forbidden: Invalid or missing secret key.\n");
    }

    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    // Determine project base directory
    $projectDir = is_dir('/home/nexviabackend') ? '/home/nexviabackend' : dirname(__DIR__);

    // Set PATH and HOME environments for web server execution
    $currentPath = getenv('PATH') ?: '/usr/bin:/bin';
    putenv("PATH={$currentPath}:/usr/local/bin:/usr/bin:/bin:/usr/local/cpanel/3rdparty/bin:/opt/cpanel/ea-php82/root/usr/bin:/opt/cpanel/ea-php83/root/usr/bin");
    putenv("HOME={$projectDir}");

    echo "=== NEXVIA DEPLOYMENT TRIGGERED ({$method}) ===\n";
    echo "Time: " . date('Y-m-d H:i:s') . "\n";
    echo "Directory: {$projectDir}\n";

    // Helper to run commands with fallback if shell_exec is disabled
    function runCmd($cmd, $cwd) {
        echo "\n----------------------------------------\n";
        echo "Running: {$cmd}\n";
        
        $disabled = explode(',', (string)ini_get('disable_functions'));
        $disabled = array_map('trim', $disabled);

        if (!in_array('exec', $disabled) && function_exists('exec')) {
            $output = [];
            $code = 0;
            @exec("cd " . escapeshellarg($cwd) . " && {$cmd} 2>&1", $output, $code);
            $res = implode("\n", $output);
            echo "Output:\n" . ($res ?: '(no output)') . "\n";
            echo "Exit code: {$code}\n";
            return $code;
        } elseif (!in_array('shell_exec', $disabled) && function_exists('shell_exec')) {
            $res = @shell_exec("cd " . escapeshellarg($cwd) . " && {$cmd} 2>&1");
            echo "Output:\n" . ($res ?: '(no output)') . "\n";
            return 0;
        } elseif (!in_array('passthru', $disabled) && function_exists('passthru')) {
            ob_start();
            @passthru("cd " . escapeshellarg($cwd) . " && {$cmd} 2>&1");
            $res = ob_get_clean();
            echo "Output:\n" . ($res ?: '(no output)') . "\n";
            return 0;
        } else {
            echo "ERROR: exec, shell_exec, and passthru are all disabled in php.ini!\n";
            return 1;
        }
    }

    // Step 1: Git safe directory
    runCmd('git config --global --add safe.directory ' . escapeshellarg($projectDir), $projectDir);
    runCmd('git config --global --add safe.directory "*"', $projectDir);

    // Step 2: Git pull
    runCmd('git pull origin main', $projectDir);

    // Detect PHP binary
    $phpBin = 'php';
    if (defined('PHP_BINARY') && is_executable(PHP_BINARY)) {
        $phpBin = PHP_BINARY;
    }

    // Step 3: Laravel commands
    runCmd("{$phpBin} artisan migrate --force", $projectDir);
    runCmd("{$phpBin} artisan config:clear", $projectDir);
    runCmd("{$phpBin} artisan cache:clear", $projectDir);

    echo "\n=== DEPLOYMENT FINISHED ===\n";
    http_response_code(200);

} catch (\Throwable $e) {
    http_response_code(200); // Return 200 so GitHub displays the complete error trace
    echo "\n=== DEPLOYMENT ENCOUNTERED AN EXCEPTION ===\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " on line " . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
