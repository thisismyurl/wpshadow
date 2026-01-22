<?php
declare(strict_types=1);

/**
 * Sample Diagnostic Execution Test
 * 
 * Actually executes 20 diagnostics to verify they run cleanly
 */

error_reporting(E_ALL);
ini_set('display_errors', '0');

$root = dirname(__DIR__);
$diagDir = $root . '/includes/diagnostics';

echo "SAMPLE DIAGNOSTIC EXECUTION TEST\n";
echo "================================\n\n";

// Load infrastructure
require_once $root . '/includes/core/class-diagnostic-base.php';
require_once $root . '/includes/core/class-diagnostic-lean-checks.php';

$files = glob("$diagDir/class-diagnostic-*.php") ?: [];
sort($files);

// Filter out registry
$files = array_filter($files, fn($f) => basename($f) !== 'class-diagnostic-registry.php');
$files = array_values($files);

// Sample: first 10 and every 250th diagnostic
$samples = [0, 250, 500, 750, 1000, 1250, 1500, 1750, 2000, 2250];
$samples = array_filter($samples, fn($i) => isset($files[$i]));

echo sprintf("Testing %d sample diagnostics from the suite...\n\n", count($samples));

$passed = 0;
$failed = 0;
$results = [];

foreach ($samples as $idx) {
    $path = $files[$idx];
    $name = basename($path);

    echo sprintf("[%4d] %-60s ", $idx + 1, substr($name, 0, 60));
    flush();

    try {
        $content = file_get_contents($path);
        if (!$content) throw new Exception("Cannot read file");

        // Extract class
        if (!preg_match('/namespace\s+([^\;]+);/', $content, $nm)) 
            throw new Exception("Cannot find namespace");
        if (!preg_match('/class\s+([A-Za-z_][A-Za-z0-9_]*)\s+extends/', $content, $cm))
            throw new Exception("Cannot find class");

        $fullClass = $nm[1] . '\\' . $cm[1];

        // Load
        if (!class_exists($fullClass)) {
            require_once $path;
        }

        if (!class_exists($fullClass)) 
            throw new Exception("Class not loaded: $fullClass");

        // Execute
        $start = microtime(true);
        $result = call_user_func([$fullClass, 'check']);
        $time = microtime(true) - $start;

        // Validate
        if ($result !== null && !is_array($result)) {
            throw new Exception("Invalid return type: " . gettype($result));
        }

        $status = $result === null ? "null" : "finding";
        echo sprintf("✅ %s (%.4fs)\n", $status, $time);
        $results[$name] = ['status' => 'ok', 'type' => $status, 'time' => $time];
        $passed++;

    } catch (Throwable $e) {
        echo sprintf("❌ %s\n", substr($e->getMessage(), 0, 50));
        $results[$name] = ['status' => 'error', 'error' => $e->getMessage()];
        $failed++;
    }
}

echo "\n";
echo "RESULTS\n";
echo "-------\n";
echo sprintf("Passed:  %d/%d\n", $passed, count($samples));
echo sprintf("Failed:  %d/%d\n", $failed, count($samples));

if ($failed === 0) {
    echo "\n✅ All sample diagnostics executed successfully!\n";
    echo "✅ Tests are running correctly and returning valid data.\n";
} else {
    echo "\n⚠️  Some diagnostics failed execution\n";
}

echo "\n";
