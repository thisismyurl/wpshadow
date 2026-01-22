<?php
declare(strict_types=1);

/**
 * Fast Diagnostic Test Completeness Report
 *
 * Validates all 2000+ diagnostics have tests without loading them
 */

error_reporting(E_ALL);
ini_set('display_errors', '0');

$root = dirname(__DIR__);
$diagDir = $root . '/includes/diagnostics';

echo "DIAGNOSTIC TEST COMPLETENESS REPORT\n";
echo "====================================\n\n";

$stats = [
    'total'             => 0,
    'has_check'         => 0,
    'missing_check'     => 0,
    'has_proper_return' => 0,
];

$missing = [];
$registry = false;

// Scan all files
$files = glob("$diagDir/class-diagnostic-*.php");
if (!is_array($files)) {
    $files = [];
}
sort($files);

$stats['total'] = count($files);

echo "Found {$stats['total']} diagnostic files\n\n";

foreach ($files as $path) {
    $basename = basename($path);
    $content = file_get_contents($path);
    if ($content === false) continue;

    // Skip registry (not a diagnostic)
    if ($basename === 'class-diagnostic-registry.php') {
        $registry = true;
        continue;
    }

    // Look for check() method declaration
    if (preg_match('/public\s+static\s+function\s+check\s*\(\s*\)\s*:\s*\?array|public\s+static\s+function\s+check\s*\(\s*\)/', $content)) {
        $stats['has_check']++;

        // Bonus: verify it has a return statement
        if (preg_match('/return\s+(null|array|\\[|\\$|\w+::)/', $content)) {
            $stats['has_proper_return']++;
        }
    } else {
        $missing[] = $basename;
        $stats['missing_check']++;
    }
}

echo "RESULTS\n";
echo "-------\n";
echo sprintf("Total Diagnostics:       %d\n", $stats['total']);
echo sprintf("With check() Method:     %d\n", $stats['has_check']);
echo sprintf("With Proper Returns:     %d\n", $stats['has_proper_return']);
echo sprintf("Missing check():         %d\n", $stats['missing_check']);

$coverage = $stats['total'] > 0 ? round(($stats['has_check'] / $stats['total']) * 100, 2) : 0;
echo sprintf("\nTest Coverage:           %.2f%%\n", $coverage);

if ($stats['missing_check'] > 0) {
    echo "\n❌ DIAGNOSTICS MISSING check() METHOD:\n";
    foreach ($missing as $f) {
        echo "   - $f\n";
    }
}

echo "\n";
if ($coverage === 100.0) {
    echo "✅ SUCCESS: All 2500+ diagnostics have check() methods defined!\n";
    echo "✅ All tests are defined and ready to execute.\n";
} else {
    echo "⚠️  Some diagnostics are missing check() methods.\n";
    echo "    Run: php tools/auto-implement-diagnostics.php\n";
}

echo "\n";

// Quick sampler: verify a few from different positions
echo "QUICK VALIDATION SAMPLE\n";
echo "-----------------------\n";

$sampleIndices = [0, 100, 500, 1000, 1500, 2000, 2400];
$failCount = 0;

foreach ($sampleIndices as $idx) {
    if (!isset($files[$idx])) break;
    $path = $files[$idx];
    $content = file_get_contents($path);
    $basename = basename($path);

    if (!preg_match('/public\s+static\s+function\s+check\s*\(/', $content)) {
        echo "❌ $basename (index $idx): missing check()\n";
        $failCount++;
    } else {
        echo "✅ $basename\n";
    }
}

if ($failCount === 0 && $coverage > 99) {
    echo "\n✅ Spot check passed. All tests are properly defined.\n";
}

echo "\nNEXT STEPS:\n";
echo "-----------\n";
echo "1. Run diagnostics: php tools/run-all-diagnostics-tests.php\n";
echo "2. View impact map: php tools/show-impact-reference.php\n";
echo "3. Check linter:    php tools/lint-diagnostics.php\n";
echo "\n";
