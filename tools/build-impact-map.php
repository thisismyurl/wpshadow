#!/usr/bin/php
<?php
/**
 * Build Impact Map for All Diagnostics
 *
 * Scans includes/diagnostics for diagnostic files, derives slugs, and
 * uses Performance_Impact_Classifier to predict impact and guardian
 * suitability for each. Outputs a JSON map at includes/data/impact-map.json
 * and prints a summary table.
 *
 * Usage:
 *   php tools/build-impact-map.php
 */

declare(strict_types=1);

// Satisfy ABSPATH guard in included classes
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__);
}

$root = dirname(__DIR__);
$includesDir = $root . '/includes';
$diagnosticsDir = $includesDir . '/diagnostics';
$dataDir = $includesDir . '/data';
$outputFile = $dataDir . '/impact-map.json';
$rulesFile = $dataDir . '/impact-rules.json';

// Load classifier
require_once $includesDir . '/core/class-performance-impact-classifier.php';

use WPShadow\Core\Performance_Impact_Classifier as PIC;

function err(string $message): void { fwrite(STDERR, $message . "\n"); }
function out(string $message): void { fwrite(STDOUT, $message . "\n"); }

function derive_slug_from_path(string $path): ?string {
    $base = basename($path);
    // Normalize to lowercase
    $lower = strtolower($base);

    // Common patterns
    if (preg_match('/^class-diagnostic-(.+)\.php$/', $lower, $m)) {
        return trim($m[1]);
    }
    if (preg_match('/^class-(?:diagnostic-)?(.+)\.php$/', $lower, $m)) {
        return trim($m[1]);
    }
    if (preg_match('/^diagnostic-(.+)\.php$/', $lower, $m)) {
        return trim($m[1]);
    }

    // Fallback: strip prefixes and suffixes
    $slug = preg_replace('/\.php$/', '', $lower);
    $slug = preg_replace('/^class-/', '', $slug);
    $slug = preg_replace('/^diagnostic-/', '', $slug);
    $slug = preg_replace('/[^a-z0-9\-]/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    $slug = trim($slug, '-');

    return $slug ?: null;
}

function load_rules(string $file): array {
    if (!is_file($file)) return [];
    $raw = @file_get_contents($file);
    if ($raw === false) return [];
    $json = json_decode($raw, true);
    return is_array($json) ? $json : [];
}

function apply_rules(string $slug, array $pred, array $rules): array {
    // Rules format supports two sections:
    // 1) exact: { slug: { impact, guardian } }
    // 2) contains: [ { needle: "backup", impact: "extreme", guardian: "manual" }, ... ]
    $impact = $pred['impact_level'] ?? 'medium';
    $guardian = $pred['guardian_suitable'] ?? 'background';

    if (!empty($rules['exact']) && isset($rules['exact'][$slug])) {
        $r = $rules['exact'][$slug];
        $impact = $r['impact'] ?? $impact;
        $guardian = $r['guardian'] ?? $guardian;
    }

    if (!empty($rules['contains']) && is_array($rules['contains'])) {
        foreach ($rules['contains'] as $r) {
            $needle = $r['needle'] ?? '';
            if (!is_string($needle) || $needle === '') continue;
            if (strpos($slug, $needle) !== false) {
                $impact = $r['impact'] ?? $impact;
                $guardian = $r['guardian'] ?? $guardian;
            }
        }
    }

    return [$impact, $guardian];
}

function scan_diagnostic_files(string $dir): array {
    $files = [];
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $file) {
        if (!$file->isFile()) continue;
        $path = $file->getPathname();
        if (substr($path, -4) !== '.php') continue;
        $files[] = $path;
    }
    return $files;
}

$start = microtime(true);

if (!is_dir($diagnosticsDir)) {
    err('Diagnostics directory not found: ' . $diagnosticsDir);
    exit(1);
}

$files = scan_diagnostic_files($diagnosticsDir);
$totalFiles = count($files);
// Load optional refinement rules
$rules = load_rules($rulesFile);

out("\n╔═══════════════════════════════════════════════════════════════════════╗");
out("║  Building Impact Map for Diagnostics                                 ║");
out("╚═══════════════════════════════════════════════════════════════════════╝\n");

out(sprintf("Scanning %d diagnostic files...", $totalFiles));

$map = [];
$byImpact = [];
$byGuardian = [];
$errors = 0;
$processed = 0;

foreach ($files as $path) {
    $slug = derive_slug_from_path($path);
    if ($slug === null || $slug === '') {
        $errors++;
        continue;
    }

    // Avoid duplicates, prefer first occurrence
    if (isset($map[$slug])) {
        continue;
    }

    $pred = PIC::predict($slug);
    [$impact, $guardian] = apply_rules($slug, $pred, $rules);

    $map[$slug] = [
        'impact'      => $impact,
        'guardian'    => $guardian,
        'factors'     => $pred['factors'] ?? [],
        'description' => $pred['description'] ?? ''
    ];

    $byImpact[$impact] = ($byImpact[$impact] ?? 0) + 1;
    $byGuardian[$guardian] = ($byGuardian[$guardian] ?? 0) + 1;
    $processed++;
}

// Ensure data directory exists
if (!is_dir($dataDir)) {
    @mkdir($dataDir, 0775, true);
}

// Write JSON atomically
$tempFile = $outputFile . '.tmp';
file_put_contents($tempFile, json_encode($map, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
@rename($tempFile, $outputFile);

$elapsed = round((microtime(true) - $start), 3);

out("");
out("SUMMARY");
out("──────────────────────────────────────────────────────────────────────");
out(sprintf("Files scanned:      %d", $totalFiles));
out(sprintf("Diagnostics mapped:  %d", $processed));
out(sprintf("Errors:             %d", $errors));
out(sprintf("Output file:        %s", $outputFile));
out(sprintf("Elapsed:            %.3f s", $elapsed));

out("");

ksort($byImpact);
ksort($byGuardian);

out("Distribution by Impact Level:");
foreach ($byImpact as $k => $v) {
    out(sprintf("  %-12s %6d", $k, $v));
}

out("");

echo "Distribution by Guardian Context:\n";
foreach ($byGuardian as $k => $v) {
    out(sprintf("  %-12s %6d", $k, $v));
}

out("\nDone.");
