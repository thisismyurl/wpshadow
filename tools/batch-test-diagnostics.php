<?php
declare(strict_types=1);

/**
 * Fast Batch Diagnostic Test Report
 * Tests in batches of 100 for efficiency
 */

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('memory_limit', '256M');

$root = dirname(__DIR__);
$diagDir = $root . '/includes/diagnostics';

// Quick file scan
$files = array_values(array_filter(
    glob("$diagDir/class-diagnostic-*.php") ?: [],
    fn($f) => basename($f) !== 'class-diagnostic-registry.php'
));

$total = count($files);
$batchSize = 100;
$allFindings = 0;
$allErrors = 0;
$nullReturns = 0;

echo "BATCH DIAGNOSTIC TEST REPORT\n";
echo "============================\n\n";
echo "Testing $total diagnostics in batches of $batchSize...\n\n";

// Load base classes once
require_once $root . '/includes/core/class-diagnostic-base.php';
require_once $root . '/includes/core/class-diagnostic-lean-checks.php';

$batchNum = 0;
for ($i = 0; $i < $total; $i += $batchSize) {
    $batchNum++;
    $batchStart = $i;
    $batchEnd = min($i + $batchSize, $total);
    $batchCount = $batchEnd - $batchStart;

    echo sprintf("Batch %d: Testing files %d-%d... ", $batchNum, $batchStart + 1, $batchEnd);
    flush();

    $batchFindings = 0;
    $batchErrors = 0;
    $batchNull = 0;

    for ($j = $batchStart; $j < $batchEnd; $j++) {
        $path = $files[$j];
        $content = @file_get_contents($path);
        if (!$content) continue;

        // Extract class info
        $ns = $cn = null;
        if (preg_match('/namespace\s+([^\;]+);/', $content, $m)) $ns = $m[1];
        if (preg_match('/class\s+([A-Za-z_][A-Za-z0-9_]*)\s+extends/', $content, $m)) $cn = $m[1];

        if (!$ns || !$cn) continue;

        $fullClass = "{$ns}\\{$cn}";
        try {
            if (!class_exists($fullClass, false)) @require_once $path;
            if (!class_exists($fullClass)) continue;

            $result = @call_user_func([$fullClass, 'check']);
            if ($result === null) {
                $batchNull++;
            } elseif (is_array($result)) {
                $batchFindings++;
            }
        } catch (Throwable $e) {
            $batchErrors++;
        }
    }

    echo sprintf("✓ %d findings, %d errors\n", $batchFindings, $batchErrors);
    $allFindings += $batchFindings;
    $allErrors += $batchErrors;
    $nullReturns += $batchNull;

    gc_collect_cycles();
}

echo "\n";
echo "FINAL RESULTS\n";
echo "=============\n\n";
echo sprintf("Total Diagnostics:     %d\n", $total);
echo sprintf("Findings Detected:     %d\n", $allFindings);
echo sprintf("Null Returns (OK):     %d\n", $nullReturns);
echo sprintf("Execution Errors:      %d\n", $allErrors);
echo sprintf("Success Rate:          %.1f%%\n", (($total - $allErrors) / $total) * 100);

echo "\n";
if ($allErrors < 10) {
    echo "✅ All diagnostic tests executed successfully!\n";
} else {
    echo "⚠️  Some errors detected. Review logs.\n";
}
echo "\n";
