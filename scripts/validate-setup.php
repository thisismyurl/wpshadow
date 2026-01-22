#!/usr/bin/env php
<?php
/**
 * Simple validation test for orchestrator output
 * 
 * Tests that the generated code is syntactically valid
 */

echo "Orchestrator Output Validation\n";
echo "==============================\n\n";

// Test 1: Check if test infrastructure exists
echo "1. Testing infrastructure...\n";
$checks = [
    'PHPUnit config' => file_exists('phpunit.xml'),
    'Test bootstrap' => file_exists('tests/bootstrap.php'),
    'Tests directory' => is_dir('tests/diagnostics'),
    'Sample test' => file_exists('tests/diagnostics/DiagnosticActiveLoginAttacksTest.php'),
];

foreach ($checks as $name => $exists) {
    echo "   " . ($exists ? "✓" : "✗") . " $name\n";
}
echo "\n";

// Test 2: Verify composer dependencies
echo "2. Composer dependencies...\n";
$composer = json_decode(file_get_contents('composer.json'), true);
$requiredDeps = ['phpunit/phpunit', 'brain/monkey', 'mockery/mockery'];
foreach ($requiredDeps as $dep) {
    $exists = isset($composer['require-dev'][$dep]);
    echo "   " . ($exists ? "✓" : "✗") . " $dep\n";
}
echo "\n";

// Test 3: Verify CI workflow
echo "3. CI Workflow...\n";
$ciExists = file_exists('.github/workflows/ci.yml');
echo "   " . ($ciExists ? "✓" : "✗") . " CI workflow exists\n";
if ($ciExists) {
    $ciContent = file_get_contents('.github/workflows/ci.yml');
    echo "   " . (strpos($ciContent, 'phpunit') !== false ? "✓" : "✗") . " PHPUnit configured\n";
    echo "   " . (strpos($ciContent, 'auto-merge') !== false ? "✓" : "✗") . " Auto-merge configured\n";
    echo "   " . (strpos($ciContent, "php-version: ['8.0'") !== false ? "✓" : "✗") . " PHP 8.0+ matrix\n";
}
echo "\n";

// Test 4: Verify orchestrator script
echo "4. Orchestrator script...\n";
$orchExists = file_exists('scripts/orchestrator.php');
echo "   " . ($orchExists ? "✓" : "✗") . " Orchestrator exists\n";
if ($orchExists) {
    $orchContent = file_get_contents('scripts/orchestrator.php');
    echo "   " . (strpos($orchContent, 'is_stub_file') !== false ? "✓" : "✗") . " Stub detection function\n";
    echo "   " . (strpos($orchContent, 'generate_implementation') !== false ? "✓" : "✗") . " Implementation generator\n";
    echo "   " . (strpos($orchContent, 'generate_test_file') !== false ? "✓" : "✗") . " Test generator\n";
    echo "   " . (strpos($orchContent, 'create_branch_and_commit') !== false ? "✓" : "✗") . " Git integration\n";
}
echo "\n";

// Test 5: Verify PR creation script
echo "5. PR creation script...\n";
$prScript = file_exists('scripts/create-batch-prs.sh');
echo "   " . ($prScript ? "✓" : "✗") . " PR script exists\n";
echo "   " . (is_executable('scripts/create-batch-prs.sh') ? "✓" : "✗") . " PR script is executable\n";
echo "\n";

// Test 6: Verify documentation
echo "6. Documentation...\n";
$docs = [
    'Main automation docs' => 'DIAGNOSTIC_AUTOMATION.md',
    'Orchestrator README' => 'scripts/ORCHESTRATOR_README.md',
];
foreach ($docs as $name => $file) {
    echo "   " . (file_exists($file) ? "✓" : "✗") . " $name\n";
}
echo "\n";

// Test 7: Sample test file syntax
echo "7. Sample test syntax validation...\n";
$sampleTest = 'tests/diagnostics/DiagnosticActiveLoginAttacksTest.php';
if (file_exists($sampleTest)) {
    exec("php -l $sampleTest 2>&1", $output, $returnCode);
    if ($returnCode === 0) {
        echo "   ✓ Sample test has valid syntax\n";
    } else {
        echo "   ✗ Sample test has syntax errors\n";
        echo "   " . implode("\n   ", $output) . "\n";
    }
} else {
    echo "   ✗ Sample test not found\n";
}
echo "\n";

echo "========================================\n";
echo "Validation complete!\n";
echo "========================================\n\n";

echo "Next steps:\n";
echo "1. Run: composer install\n";
echo "2. Run: php scripts/orchestrator.php --dry-run\n";
echo "3. Run: php scripts/orchestrator.php --batch-size=10\n";
echo "4. Run: ./scripts/create-batch-prs.sh\n";
