<?php
declare(strict_types=1);

/**
 * Comprehensive Test Runner for All 2000+ Diagnostics
 *
 * This tool:
 * 1. Scans all diagnostic files
 * 2. Verifies each has a check() method
 * 3. Executes all check() methods and captures results
 * 4. Reports comprehensive statistics and any failures
 */

$root = dirname(__DIR__);
$diagDir = $root . '/includes/diagnostics';

// Track results
$stats = [
    'total_files'       => 0,
    'valid_classes'     => 0,
    'missing_check'     => [],
    'check_errors'      => [],
    'findings_count'    => 0,
    'null_returns'      => 0,
    'executed'          => 0,
    'by_family'         => [],
    'by_category'       => [],
];

// Load base class manually if needed
$baseFile = $root . '/includes/core/class-diagnostic-base.php';
if (file_exists($baseFile)) {
    require_once $baseFile;
}

$leanFile = $root . '/includes/core/class-diagnostic-lean-checks.php';
if (file_exists($leanFile)) {
    require_once $leanFile;
}

function extract_class_from_file(string $path): ?string {
    $content = file_get_contents($path);
    if ($content === false) return null;
    if (!preg_match('/class\s+([A-Za-z_][A-Za-z0-9_]*)\s+extends\s+Diagnostic_Base/', $content, $m)) {
        return null;
    }
    return $m[1];
}

function get_file_namespace(string $path): string {
    $content = file_get_contents($path);
    if ($content === false) return '';
    if (!preg_match('/namespace\s+([^\;]+);/', $content, $m)) {
        return '';
    }
    return $m[1];
}

// Scan all diagnostic files
$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($diagDir));
$files = [];
foreach ($rii as $file) {
    if ($file->isDir()) continue;
    $path = $file->getPathname();
    if (!preg_match('/class-diagnostic-.*\.php$/', $path)) continue;
    $files[] = $path;
}

sort($files);
$stats['total_files'] = count($files);

fwrite(STDOUT, "======================================\n");
fwrite(STDOUT, "Diagnostic Test Runner\n");
fwrite(STDOUT, "======================================\n");
fwrite(STDOUT, sprintf("Scanning %d diagnostic files...\n\n", $stats['total_files']));

// Process each file
$errors = [];
foreach ($files as $path) {
    $className = extract_class_from_file($path);
    if ($className === null) {
        $errors[] = "Cannot extract class: $path";
        continue;
    }

    $namespace = get_file_namespace($path);
    if (empty($namespace)) {
        $errors[] = "Cannot extract namespace: $path";
        continue;
    }

    $fullClass = "{$namespace}\\{$className}";

    // Try to load the file
    try {
        if (!class_exists($fullClass)) {
            require_once $path;
        }

        if (!class_exists($fullClass)) {
            $errors[] = "Class not loaded after require: $fullClass";
            continue;
        }

        // Verify check() exists
        if (!method_exists($fullClass, 'check')) {
            $stats['missing_check'][] = [
                'class' => $fullClass,
                'file'  => $path,
            ];
            continue;
        }

        $stats['valid_classes']++;

        // Execute check()
        try {
            $result = call_user_func([$fullClass, 'check']);

            // Categorize result
            if ($result === null) {
                $stats['null_returns']++;
            } elseif (is_array($result)) {
                $stats['findings_count']++;

                // Track by category
                $cat = $result['category'] ?? 'uncategorized';
                if (!isset($stats['by_category'][$cat])) {
                    $stats['by_category'][$cat] = 0;
                }
                $stats['by_category'][$cat]++;

                // Track by family
                $family = $result['family'] ?? 'unspecified';
                if (!isset($stats['by_family'][$family])) {
                    $stats['by_family'][$family] = 0;
                }
                $stats['by_family'][$family]++;
            } else {
                $stats['check_errors'][] = [
                    'class'  => $fullClass,
                    'reason' => 'Unexpected return type: ' . gettype($result),
                ];
            }
            $stats['executed']++;
        } catch (Throwable $e) {
            $stats['check_errors'][] = [
                'class'  => $fullClass,
                'reason' => $e->getMessage(),
            ];
        }
    } catch (Throwable $e) {
        $errors[] = "Error loading $fullClass: " . $e->getMessage();
    }
}

// Output results
fwrite(STDOUT, "======================================\n");
fwrite(STDOUT, "RESULTS\n");
fwrite(STDOUT, "======================================\n\n");

fwrite(STDOUT, sprintf("Total Files Scanned:        %d\n", $stats['total_files']));
fwrite(STDOUT, sprintf("Valid Classes Found:        %d\n", $stats['valid_classes']));
fwrite(STDOUT, sprintf("Executed Successfully:      %d\n", $stats['executed']));
fwrite(STDOUT, sprintf("  → Returned Findings:      %d\n", $stats['findings_count']));
fwrite(STDOUT, sprintf("  → Returned NULL (OK):     %d\n", $stats['null_returns']));
fwrite(STDOUT, "\n");

if (!empty($stats['missing_check'])) {
    fwrite(STDOUT, sprintf("Missing check() method:     %d\n", count($stats['missing_check'])));
}
if (!empty($stats['check_errors'])) {
    fwrite(STDOUT, sprintf("Execution Errors:           %d\n", count($stats['check_errors'])));
}

if (!empty($errors)) {
    fwrite(STDOUT, sprintf("Loading Errors:             %d\n", count($errors)));
}

// Distribution by category
if (!empty($stats['by_category'])) {
    fwrite(STDOUT, "\n--- Findings by Category ---\n");
    foreach ($stats['by_category'] as $cat => $count) {
        fwrite(STDOUT, sprintf("  %s: %d\n", $cat, $count));
    }
}

// Distribution by family
if (!empty($stats['by_family'])) {
    fwrite(STDOUT, "\n--- Findings by Family ---\n");
    arsort($stats['by_family']);
    foreach ($stats['by_family'] as $family => $count) {
        fwrite(STDOUT, sprintf("  %s: %d\n", $family, $count));
    }
}

// Report issues
if (!empty($stats['missing_check'])) {
    fwrite(STDOUT, "\n======================================\n");
    fwrite(STDOUT, "MISSING check() METHODS (" . count($stats['missing_check']) . ")\n");
    fwrite(STDOUT, "======================================\n");
    foreach (array_slice($stats['missing_check'], 0, 20) as $item) {
        fwrite(STDOUT, "  " . $item['class'] . "\n");
    }
    if (count($stats['missing_check']) > 20) {
        fwrite(STDOUT, sprintf("  ... and %d more\n", count($stats['missing_check']) - 20));
    }
}

if (!empty($stats['check_errors'])) {
    fwrite(STDOUT, "\n======================================\n");
    fwrite(STDOUT, "EXECUTION ERRORS (" . count($stats['check_errors']) . ")\n");
    fwrite(STDOUT, "======================================\n");
    foreach (array_slice($stats['check_errors'], 0, 20) as $item) {
        fwrite(STDOUT, sprintf("  %s\n    Reason: %s\n", $item['class'], $item['reason']));
    }
    if (count($stats['check_errors']) > 20) {
        fwrite(STDOUT, sprintf("  ... and %d more\n", count($stats['check_errors']) - 20));
    }
}

if (!empty($errors)) {
    fwrite(STDOUT, "\n======================================\n");
    fwrite(STDOUT, "LOADING ERRORS (" . count($errors) . ")\n");
    fwrite(STDOUT, "======================================\n");
    foreach (array_slice($errors, 0, 20) as $err) {
        fwrite(STDOUT, "  $err\n");
    }
    if (count($errors) > 20) {
        fwrite(STDOUT, sprintf("  ... and %d more\n", count($errors) - 20));
    }
}

// Final summary
fwrite(STDOUT, "\n======================================\n");
fwrite(STDOUT, "SUMMARY\n");
fwrite(STDOUT, "======================================\n");

$successRate = $stats['total_files'] > 0 ? round(($stats['executed'] / $stats['total_files']) * 100, 2) : 0;
fwrite(STDOUT, sprintf("Success Rate: %.2f%%\n", $successRate));

if ($stats['executed'] === $stats['total_files']) {
    fwrite(STDOUT, "✅ All diagnostics executed successfully!\n");
} else {
    $failCount = $stats['total_files'] - $stats['executed'];
    fwrite(STDOUT, sprintf("⚠️  %d diagnostics did not execute fully.\n", $failCount));
}

fwrite(STDOUT, "\n");
