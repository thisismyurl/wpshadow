#!/usr/bin/php
<?php
/**
 * Lint Diagnostics for WPShadow Guidelines Compliance
 *
 * Scans includes/diagnostics for:
 *  - strict_types declaration
 *  - namespace WPShadow\Diagnostics
 *  - class definition
 *  - presence of get_name(), get_description(), run()
 *  - KB link in description string
 *  - Optional: KPI_Tracker usage
 *  - No eval(), no direct SQL patterns
 *
 * Usage:
 *   php tools/lint-diagnostics.php [--fix-strict-types]
 */

declare(strict_types=1);

$root = dirname(__DIR__);
$dir = $root . '/includes/diagnostics';
$fixStrict = in_array('--fix-strict-types', $argv, true);

if (!is_dir($dir)) {
    fwrite(STDERR, "Diagnostics directory not found: $dir\n");
    exit(1);
}

$files = [];
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
foreach ($it as $file) {
    if ($file->isFile() && substr($file->getFilename(), -4) === '.php') {
        $files[] = $file->getPathname();
    }
}

$total = count($files);
$issues = 0;
$report = [];

function has_strict_types(string $code): bool {
    return (bool)preg_match('/declare\s*\(\s*strict_types\s*=\s*1\s*\)\s*;/', $code);
}
function has_namespace_diag(string $code): bool {
    return (bool)preg_match('/namespace\s+WPShadow\\\\Diagnostics\s*;/', $code);
}
function has_class(string $code): bool {
    return (bool)preg_match('/class\s+\w+\s+extends\s+\\?WPShadow\\\\Core\\\\Diagnostic_Base/', $code) ||
           (bool)preg_match('/class\s+\w+\s+extends\s+Diagnostic_Base/', $code);
}
function has_method(string $code, string $name): bool {
    return (bool)preg_match('/function\s+' . preg_quote($name, '/') . '\s*\(/', $code);
}
function has_kb_link(string $code): bool {
    return (bool)preg_match('#https?://[^\s\"\']+/kb/#', $code);
}
function uses_kpi_tracker(string $code): bool {
    return (bool)preg_match('/KPI_Tracker::record_/', $code);
}
function has_forbidden_eval(string $code): bool {
    return (bool)preg_match('/\beval\s*\(/', $code);
}
function has_raw_sql(string $code): bool {
    return (bool)preg_match('/\$wpdb\s*->\s*query\s*\(/', $code);
}

foreach ($files as $path) {
    $code = file_get_contents($path) ?: '';
    $fileIssues = [];

    if (!has_strict_types($code)) {
        $fileIssues[] = 'missing_strict_types';
        if ($fixStrict) {
            $code = "<?php\ndeclare(strict_types=1);\n" . preg_replace('/^<\?php\s*/', '', $code);
            file_put_contents($path, $code);
        }
    }
    if (!has_namespace_diag($code)) $fileIssues[] = 'missing_namespace_WPShadow\\Diagnostics';
    if (!has_class($code)) $fileIssues[] = 'missing_class_extends_Diagnostic_Base';
    if (!has_method($code, 'get_name')) $fileIssues[] = 'missing_get_name';
    if (!has_method($code, 'get_description')) $fileIssues[] = 'missing_get_description';
    if (!has_method($code, 'run')) $fileIssues[] = 'missing_run';
    if (!has_kb_link($code)) $fileIssues[] = 'missing_kb_link';
    if (!uses_kpi_tracker($code)) $fileIssues[] = 'missing_kpi_tracker_usage (recommended)';
    if (has_forbidden_eval($code)) $fileIssues[] = 'forbidden_eval_found';
    if (has_raw_sql($code)) $fileIssues[] = 'raw_sql_query_detected (review)';

    if ($fileIssues) {
        $issues += count($fileIssues);
        $report[$path] = $fileIssues;
    }
}

// Output summary
fwrite(STDOUT, "\nDiagnostics Lint Report\n");
fwrite(STDOUT, "────────────────────────────────────────────────────────\n");
fwrite(STDOUT, sprintf("Files scanned:   %d\n", $total));
fwrite(STDOUT, sprintf("Files with issues: %d\n", count($report)));

$top = 0;
foreach ($report as $path => $problems) {
    fwrite(STDOUT, "\n• " . $path . "\n");
    foreach ($problems as $p) {
        fwrite(STDOUT, "  - " . $p . "\n");
    }
    if (++$top >= 50) {
        fwrite(STDOUT, "\n(Showing first 50 files. Use grep or edit the script to expand.)\n");
        break;
    }
}

fwrite(STDOUT, "\nTip: Run with --fix-strict-types to auto-add strict types where missing.\n");
