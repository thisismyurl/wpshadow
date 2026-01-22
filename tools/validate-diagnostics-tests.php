<?php
declare(strict_types=1);

/**
 * Lean Test Validator for All Diagnostics
 *
 * Efficiently validates that all 2000+ diagnostics:
 * 1. Have a check() method
 * 2. Are properly structured (namespace, extends)
 * 3. Execute without errors (basic smoke test)
 */

error_reporting(E_ALL);
ini_set('display_errors', '0');

$root = dirname(__DIR__);
$diagDir = $root . '/includes/diagnostics';

$stats = [
    'total'             => 0,
    'has_check'         => 0,
    'missing_check'     => 0,
    'malformed'         => 0,
    'samples_executed'  => 0,
    'execution_errors'  => 0,
];

// Scan all files
$files = [];
$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($diagDir));
foreach ($rii as $file) {
    if ($file->isDir()) continue;
    $path = $file->getPathname();
    if (!preg_match('/class-diagnostic-.*\.php$/', $path)) continue;
    $files[] = $path;
}

$stats['total'] = count($files);
sort($files);

echo "DIAGNOSTIC TEST VALIDATION REPORT\n";
echo "==================================\n\n";
echo "Scanning {$stats['total']} diagnostic files...\n\n";

$missing = [];
$malformed_files = [];

// First pass: structure validation
foreach ($files as $path) {
    $content = file_get_contents($path);
    if ($content === false) {
        $malformed_files[] = basename($path);
        $stats['malformed']++;
        continue;
    }

    // Check for class declaration
    if (!preg_match('/class\s+[A-Za-z_][A-Za-z0-9_]*\s+extends\s+Diagnostic_Base/', $content)) {
        $malformed_files[] = basename($path);
        $stats['malformed']++;
        continue;
    }

    // Check for check() method
    if (preg_match('/public\s+static\s+function\s+check\s*\(/', $content)) {
        $stats['has_check']++;
    } else {
        $missing[] = basename($path);
        $stats['missing_check']++;
    }
}

echo "STRUCTURE VALIDATION\n";
echo "---------------------\n";
echo "Total Files:              {$stats['total']}\n";
echo "With check() Method:      {$stats['has_check']}\n";
echo "Missing check() Method:   {$stats['missing_check']}\n";
echo "Malformed/Invalid:        {$stats['malformed']}\n\n";

if ($stats['missing_check'] > 0) {
    echo "FILES MISSING check() (" . $stats['missing_check'] . " total):\n";
    foreach (array_slice($missing, 0, 25) as $f) {
        echo "  - $f\n";
    }
    if (count($missing) > 25) {
        echo "  ... and " . (count($missing) - 25) . " more\n";
    }
    echo "\n";
}

if ($stats['malformed'] > 0) {
    echo "MALFORMED FILES (" . $stats['malformed'] . " total):\n";
    foreach (array_slice($malformed_files, 0, 15) as $f) {
        echo "  - $f\n";
    }
    if (count($malformed_files) > 15) {
        echo "  ... and " . (count($malformed_files) - 15) . " more\n";
    }
    echo "\n";
}

// Sample execution (first 50 diagnostics with check())
echo "SAMPLE EXECUTION TEST\n";
echo "---------------------\n";
echo "Testing first 50 diagnostics with check()...\n\n";

require_once $root . '/includes/core/class-diagnostic-base.php';
require_once $root . '/includes/core/class-diagnostic-lean-checks.php';

$tested = 0;
$errors = [];

foreach ($files as $path) {
    if ($tested >= 50) break;

    $content = file_get_contents($path);
    if ($content === false) continue;
    if (!preg_match('/public\s+static\s+function\s+check\s*\(/', $content)) continue;

    // Extract namespace and class
    if (!preg_match('/namespace\s+([^\;]+);/', $content, $nm)) continue;
    if (!preg_match('/class\s+([A-Za-z_][A-Za-z0-9_]*)\s+extends/', $content, $cm)) continue;

    $ns = $nm[1];
    $cn = $cm[1];
    $fullClass = "{$ns}\\{$cn}";

    try {
        if (!class_exists($fullClass)) {
            require_once $path;
        }

        if (!class_exists($fullClass)) {
            $errors[] = "$fullClass: Class not loaded";
            continue;
        }

        if (!method_exists($fullClass, 'check')) {
            $errors[] = "$fullClass: check() method missing";
            continue;
        }

        // Execute
        $result = call_user_func([$fullClass, 'check']);

        // Validate result type
        if ($result !== null && !is_array($result)) {
            $errors[] = "$fullClass: check() returned " . gettype($result) . " instead of array|null";
        }

        $stats['samples_executed']++;
        echo ".";
        flush();
    } catch (Throwable $e) {
        $stats['execution_errors']++;
        $errors[] = "$fullClass: " . $e->getMessage();
        echo "E";
        flush();
    }

    $tested++;
}

echo "\n\n";

if (count($errors) > 0) {
    echo "SAMPLE EXECUTION ERRORS (" . count($errors) . "):\n";
    foreach (array_slice($errors, 0, 20) as $err) {
        echo "  ⚠️  $err\n";
    }
    if (count($errors) > 20) {
        echo "  ... and " . (count($errors) - 20) . " more\n";
    }
    echo "\n";
}

// Summary
echo "SUMMARY\n";
echo "-------\n";
$pct = $stats['total'] > 0 ? round(($stats['has_check'] / $stats['total']) * 100, 2) : 0;
echo "Coverage: {$pct}% ({$stats['has_check']}/{$stats['total']})\n";

if ($stats['missing_check'] === 0 && $stats['malformed'] === 0) {
    echo "✅ All diagnostics are properly structured with check() methods!\n";
} else {
    $issues = $stats['missing_check'] + $stats['malformed'];
    echo "⚠️  $issues issues found (missing check() or malformed)\n";
}

if ($stats['execution_errors'] === 0 && $stats['samples_executed'] > 0) {
    echo "✅ Sample execution test passed ({$stats['samples_executed']} executed)\n";
} else {
    echo "⚠️  {$stats['execution_errors']} execution errors\n";
}

echo "\n";
