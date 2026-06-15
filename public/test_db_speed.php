<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

header('Content-Type: text/plain');
echo "=== Database Latency Test ===\n";
$host = config('database.connections.mysql.host');
$port = config('database.connections.mysql.port');
$database = config('database.connections.mysql.database');

echo "DB_HOST: " . $host . "\n";
echo "DB_PORT: " . $port . "\n";
echo "DB_DATABASE: " . $database . "\n";

echo "\n=== Network Ping (TCP Connect RTT) ===\n";
$fs_start = microtime(true);
$fp = @fsockopen($host, $port, $errno, $errstr, 5);
if ($fp) {
    $fs_time = (microtime(true) - $fs_start) * 1000;
    echo "fsockopen Connect Latency: " . number_format($fs_time, 2) . " ms\n";
    fclose($fp);
} else {
    echo "fsockopen Connect Failed: $errstr ($errno)\n";
}

echo "\n=== Database Query Latency ===\n";
try {
    $start = microtime(true);
    DB::connection()->getPdo();
    $conn_time = (microtime(true) - $start) * 1000;
    echo "PDO Connection Time: " . number_format($conn_time, 2) . " ms\n";
    
    $times = [];
    for ($i = 0; $i < 20; $i++) {
        $q_start = microtime(true);
        DB::select("SELECT 1");
        $times[] = (microtime(true) - $q_start) * 1000;
    }
    
    echo "Query Latency (20 runs):\n";
    foreach ($times as $idx => $t) {
        echo "  Run " . ($idx + 1) . ": " . number_format($t, 2) . " ms\n";
    }
    echo "Average Query Latency: " . number_format(array_sum($times) / count($times), 2) . " ms\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
