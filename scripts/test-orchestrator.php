#!/usr/bin/env php
<?php
/**
 * Test Script - Validates orchestrator functions on a single file
 * 
 * Usage: php scripts/test-orchestrator.php
 */

// Include orchestrator functions
define('ROOT_DIR', dirname(__DIR__));
define('DIAGNOSTICS_DIR', ROOT_DIR . '/includes/diagnostics');
define('TESTS_DIR', ROOT_DIR . '/tests/diagnostics');

// Load the orchestrator functions (but don't execute main logic)
$orchestratorCode = file_get_contents(__DIR__ . '/orchestrator.php');
// Remove the main execution part
$orchestratorCode = preg_replace('/^\/\/ Main execution.*$/ms', '', $orchestratorCode);
eval('?>' . $orchestratorCode);

echo "Testing Orchestrator Functions\n";
echo "===============================\n\n";

// Test file
$testFile = DIAGNOSTICS_DIR . '/design/class-diagnostic-css-in-js-performance.php';

if (!file_exists($testFile)) {
    echo "Error: Test file not found: $testFile\n";
    exit(1);
}

echo "Test file: " . basename($testFile) . "\n\n";

// Test 1: Stub detection
echo "1. Testing stub detection...\n";
$isStub = is_stub_file($testFile);
echo "   Result: " . ($isStub ? "STUB" : "NOT A STUB") . "\n\n";

// Test 2: Metadata extraction
echo "2. Testing metadata extraction...\n";
$metadata = extract_diagnostic_metadata($testFile);
echo "   Class: {$metadata['className']}\n";
echo "   Slug: {$metadata['slug']}\n";
echo "   Category: {$metadata['category']}\n";
echo "   Title: {$metadata['title']}\n\n";

// Test 3: Implementation generation
echo "3. Testing implementation generation...\n";
$implementation = generate_implementation($metadata);
echo "   Generated " . strlen($implementation) . " characters\n";
echo "   Preview:\n";
echo "   " . str_replace("\n", "\n   ", substr($implementation, 0, 300)) . "...\n\n";

// Test 4: Test file generation
echo "4. Testing test file generation...\n";
$testCode = generate_test_file($metadata);
echo "   Generated " . strlen($testCode) . " characters\n";
echo "   Has correct namespace: " . (strpos($testCode, 'namespace') !== false ? "YES" : "NO") . "\n";
echo "   Has PHPUnit TestCase: " . (strpos($testCode, 'extends TestCase') !== false ? "YES" : "NO") . "\n";
echo "   Has Brain Monkey setup: " . (strpos($testCode, 'Monkey\\setUp()') !== false ? "YES" : "NO") . "\n\n";

// Test 5: Syntax validation
echo "5. Testing generated code syntax...\n";
$tempFile = tempnam(sys_get_temp_dir(), 'wpshadow_test_');
file_put_contents($tempFile, $testCode);
exec("php -l $tempFile 2>&1", $output, $returnCode);
if ($returnCode === 0) {
    echo "   ✓ Test code syntax is valid\n";
} else {
    echo "   ✗ Test code has syntax errors:\n";
    echo "   " . implode("\n   ", $output) . "\n";
}
unlink($tempFile);

echo "\n";
echo "All tests completed!\n";
