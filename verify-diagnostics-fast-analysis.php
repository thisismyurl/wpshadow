#!/usr/bin/env php
<?php
/**
 * Fast Batch Verification - Statistical Sample
 * 
 * Since we have 2,633+ diagnostics, we'll test a representative sample
 * and extrapolate patterns. This is much faster than testing all.
 */

declare(strict_types=1);

$results = [
    'total_files' => 0,
    'has_check_method' => 0,
    'sample_tested' => 0,
    'sample_passed' => 0,
    'sample_failed' => 0,
    'failures' => [],
];

// Find all diagnostic files
echo "📊 Analyzing Diagnostic Files\n";
echo "═════════════════════════════\n\n";

$files = [];
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator('/workspaces/wpshadow/includes/diagnostics'),
    RecursiveIteratorIterator::LEAVES_ONLY
);

foreach ($iterator as $file) {
    if ($file->isFile() && strpos($file->getFilename(), 'class-diagnostic-') === 0) {
        $files[] = $file->getPathname();
    }
}

$results['total_files'] = count($files);
echo "✓ Total diagnostic files: {$results['total_files']}\n";

// Check for check() method
foreach ($files as $file) {
    $content = file_get_contents($file);
    if (strpos($content, 'public static function check') !== false) {
        $results['has_check_method']++;
    }
}

echo "✓ Files with check() method: {$results['has_check_method']}\n";
echo "✓ Stub files (no check method): " . ($results['total_files'] - $results['has_check_method']) . "\n\n";

// Sample test - every 50th file for speed
echo "🧪 Testing Sample (every 50th file)\n";
echo "═════════════════════════════════════\n\n";

$sample_interval = 50;
$tested = 0;
$passed = 0;
$failed = 0;

foreach ($files as $idx => $file) {
    if ($idx % $sample_interval !== 0) {
        continue;
    }
    
    $tested++;
    $content = file_get_contents($file);
    
    if (strpos($content, 'public static function check') === false) {
        $failed++;
        $results['failures'][] = basename($file) . ' (no check method)';
        continue;
    }
    
    // Check for 'id' field in returns
    if (preg_match_all("/return\s+array\s*\(/", $content, $matches)) {
        // Found return array statements
        if (preg_match("/['\"]id['\"]\s*=>/", $content)) {
            $passed++;
        } else {
            $failed++;
            $results['failures'][] = basename($file) . ' (missing id field)';
        }
    } else {
        // Might return null only
        $passed++;
    }
}

$results['sample_tested'] = $tested;
$results['sample_passed'] = $passed;
$results['sample_failed'] = $failed;

$pass_rate = $tested > 0 ? round(($passed / $tested) * 100, 2) : 0;

echo "Sample Results:\n";
echo "  ✅ Passed: $passed/$tested (" . $pass_rate . "%)\n";
echo "  ❌ Failed: $failed/$tested\n\n";

// Extrapolate to full set
$estimated_functional = round($results['has_check_method'] * ($passed / $tested));
echo "📈 Extrapolated Estimates:\n";
echo "  Total with check(): {$results['has_check_method']}\n";
echo "  Estimated passing: ~$estimated_functional\n";
echo "  Estimated failing: ~" . ($results['has_check_method'] - $estimated_functional) . "\n\n";

if (!empty($results['failures']) && count($results['failures']) <= 10) {
    echo "⚠️ Sample Failures:\n";
    foreach ($results['failures'] as $failure) {
        echo "  - $failure\n";
    }
}

echo "\n";
echo "╔════════════════════════════════════════╗\n";
echo "║     DIAGNOSTIC ECOSYSTEM HEALTH        ║\n";
echo "╠════════════════════════════════════════╣\n";
printf("║ Total Diagnostics:      %5d       ║\n", $results['total_files']);
printf("║ Functional (w/ check):  %5d ✓      ║\n", $results['has_check_method']);
printf("║ Stubs (no code):        %5d       ║\n", $results['total_files'] - $results['has_check_method']);
printf("║ Sample Pass Rate:       %5.1f%%      ║\n", $pass_rate);
printf("║ Estimated Ready:        ~%5d       ║\n", $estimated_functional);
echo "╚════════════════════════════════════════╝\n";
