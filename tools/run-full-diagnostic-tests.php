<?php
declare(strict_types=1);

/**
 * Full Diagnostic Execution Test Suite
 *
 * Executes all 2500+ diagnostics check() methods and reports results
 * Uses minimal bootstrap to avoid memory bloat
 */

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('memory_limit', '512M');

$root = dirname(__DIR__);
$diagDir = $root . '/includes/diagnostics';

$results = [
    'total_files'      => 0,
    'executed'         => 0,
    'null_returns'     => 0,
    'findings'         => 0,
    'errors'           => 0,
    'by_category'      => [],
    'by_severity'      => [],
    'execution_times'  => [],
];

$errors = [];

echo "FULL DIAGNOSTIC EXECUTION TEST\n";
echo "===============================\n\n";

// Load base infrastructure
require_once $root . '/includes/core/class-diagnostic-base.php';
require_once $root . '/includes/core/class-diagnostic-lean-checks.php';

// Collect all diagnostic files
$files = glob("$diagDir/class-diagnostic-*.php");
if (!is_array($files)) {
    $files = [];
}
sort($files);

$results['total_files'] = count($files);
echo "Executing {$results['total_files']} diagnostics...\n";
echo "This may take a moment...\n\n";

$startTime = microtime(true);
$lastReport = 0;

foreach ($files as $idx => $path) {
    $basename = basename($path);

    // Skip registry
    if ($basename === 'class-diagnostic-registry.php') {
        continue;
    }

    // Show progress every 250 files
    if (($idx % 250) === 0 && $idx > 0) {
        $elapsed = microtime(true) - $startTime;
        echo sprintf("  Progress: %d/%d (%.1f%%) - %.2fs elapsed\n",
            $results['executed'],
            $results['total_files'] - 1,
            round(($results['executed'] / ($results['total_files'] - 1)) * 100, 1),
            $elapsed
        );
    }

    $content = file_get_contents($path);
    if ($content === false) continue;

    // Extract namespace and class
    if (!preg_match('/namespace\s+([^\;]+);/', $content, $nmatch)) continue;
    if (!preg_match('/class\s+([A-Za-z_][A-Za-z0-9_]*)\s+extends\s+Diagnostic_Base/', $content, $cmatch)) continue;

    $ns = $nmatch[1];
    $cn = $cmatch[1];
    $fullClass = "{$ns}\\{$cn}";

    try {
        // Load class if not already loaded
        if (!class_exists($fullClass)) {
            require_once $path;
        }

        if (!class_exists($fullClass)) {
            $errors[] = "$cn: Class not loaded";
            $results['errors']++;
            continue;
        }

        if (!method_exists($fullClass, 'check')) {
            $errors[] = "$cn: check() method not found";
            $results['errors']++;
            continue;
        }

        // Execute check()
        $methodStart = microtime(true);
        $result = call_user_func([$fullClass, 'check']);
        $methodTime = microtime(true) - $methodStart;
        $results['execution_times'][] = $methodTime;

        if ($result === null) {
            $results['null_returns']++;
        } elseif (is_array($result)) {
            $results['findings']++;

            // Track by category
            $cat = $result['category'] ?? 'unknown';
            if (!isset($results['by_category'][$cat])) {
                $results['by_category'][$cat] = 0;
            }
            $results['by_category'][$cat]++;

            // Track by severity
            $sev = $result['severity'] ?? 'unknown';
            if (!isset($results['by_severity'][$sev])) {
                $results['by_severity'][$sev] = 0;
            }
            $results['by_severity'][$sev]++;
        } else {
            $errors[] = "$cn: Unexpected return type " . gettype($result);
            $results['errors']++;
        }

        $results['executed']++;
    } catch (Throwable $e) {
        $results['errors']++;
        $msg = substr($e->getMessage(), 0, 100);
        $errors[] = "$cn: " . $msg;
    }

    // Free memory
    if (($idx % 100) === 0) {
        gc_collect_cycles();
    }
}

$totalTime = microtime(true) - $startTime;

echo "\n";
echo "EXECUTION COMPLETE\n";
echo "==================\n\n";

echo sprintf("Total Files:           %d\n", $results['total_files']);
echo sprintf("Successfully Executed: %d\n", $results['executed']);
echo sprintf("Null Returns (no finding): %d\n", $results['null_returns']);
echo sprintf("Findings Detected:     %d\n", $results['findings']);
echo sprintf("Execution Errors:      %d\n", $results['errors']);
echo sprintf("\nTotal Time:            %.2f seconds\n", $totalTime);
if ($results['execution_times']) {
    $avgTime = array_sum($results['execution_times']) / count($results['execution_times']);
    echo sprintf("Avg Time per Test:     %.4f seconds\n", $avgTime);
}

echo "\n";

// Results by category
if (!empty($results['by_category'])) {
    echo "Findings by Category:\n";
    arsort($results['by_category']);
    foreach (array_slice($results['by_category'], 0, 15) as $cat => $count) {
        echo sprintf("  %s: %d\n", $cat, $count);
    }
    if (count($results['by_category']) > 15) {
        echo sprintf("  ... and %d more categories\n", count($results['by_category']) - 15);
    }
    echo "\n";
}

// Results by severity
if (!empty($results['by_severity'])) {
    echo "Findings by Severity:\n";
    $order = ['critical' => 0, 'high' => 1, 'medium' => 2, 'low' => 3, 'info' => 4];
    uasort($results['by_severity'], function($a, $b) use ($order) {
        $akey = array_search(array_key_first([$a]), $order) ?? 999;
        $bkey = array_search(array_key_first([$b]), $order) ?? 999;
        return $akey <=> $bkey;
    });
    foreach ($results['by_severity'] as $sev => $count) {
        echo sprintf("  %s: %d\n", $sev, $count);
    }
    echo "\n";
}

// Errors
if (count($errors) > 0) {
    echo "Execution Errors (" . count($errors) . "):\n";
    foreach (array_slice($errors, 0, 30) as $err) {
        echo "  ⚠️  $err\n";
    }
    if (count($errors) > 30) {
        echo sprintf("  ... and %d more\n", count($errors) - 30);
    }
    echo "\n";
}

// Summary
echo "SUMMARY\n";
echo "-------\n";
$rate = $results['total_files'] > 0 ? round(($results['executed'] / $results['total_files']) * 100, 2) : 0;
echo sprintf("Execution Rate: %.2f%%\n", $rate);

if ($rate >= 99.0 && $results['errors'] < 5) {
    echo "✅ All diagnostics executed successfully!\n";
    echo "✅ Test suite is complete and running properly.\n";
} else {
    echo "⚠️  Some issues detected. Review errors above.\n";
}

echo "\n";
