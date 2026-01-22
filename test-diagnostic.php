<?php
/**
 * Diagnostic Test Harness
 * 
 * Tests individual diagnostics to verify they work correctly
 * Usage: wp eval-file test-diagnostic.php
 */

declare(strict_types=1);

// Load WordPress
if (!defined('ABSPATH')) {
    define('ABSPATH', '/var/www/html/');
    require_once ABSPATH . 'wp-load.php';
}

// Test diagnostics and log results
class Diagnostic_Tester {
    private $results = [];
    private $current_diagnostic = '';
    
    public function test_diagnostic($class_name) {
        $this->current_diagnostic = $class_name;
        $full_class = 'WPShadow\\Diagnostics\\' . $class_name;
        
        if (!class_exists($full_class)) {
            $this->log_fail("Class not found: $full_class");
            return false;
        }
        
        try {
            // Call the check method
            $result = call_user_func([$full_class, 'check']);
            
            // Verify return type
            if ($result === null) {
                $this->log_pass("Returns null (no issues)");
                return true;
            } elseif (is_array($result)) {
                // Verify required fields
                $required_fields = ['id', 'title', 'description', 'category', 'severity'];
                $missing_fields = [];
                foreach ($required_fields as $field) {
                    if (!isset($result[$field])) {
                        $missing_fields[] = $field;
                    }
                }
                
                if (!empty($missing_fields)) {
                    $this->log_fail("Missing required fields: " . implode(', ', $missing_fields));
                    return false;
                }
                
                $this->log_pass("Returns array with all required fields");
                return true;
            } else {
                $this->log_fail("Invalid return type: " . gettype($result));
                return false;
            }
        } catch (Exception $e) {
            $this->log_fail("Exception: " . $e->getMessage());
            return false;
        }
    }
    
    private function log_pass($message) {
        echo "✅ {$this->current_diagnostic}: {$message}\n";
        $this->results[$this->current_diagnostic] = 'PASS';
    }
    
    private function log_fail($message) {
        echo "❌ {$this->current_diagnostic}: {$message}\n";
        $this->results[$this->current_diagnostic] = 'FAIL';
    }
    
    public function get_summary() {
        $passed = array_filter($this->results, fn($v) => $v === 'PASS');
        $failed = array_filter($this->results, fn($v) => $v === 'FAIL');
        
        echo "\n===========================================\n";
        echo "✅ Passed: " . count($passed) . "\n";
        echo "❌ Failed: " . count($failed) . "\n";
        echo "📊 Total: " . count($this->results) . "\n";
        echo "===========================================\n";
    }
}

// Create tester
$tester = new Diagnostic_Tester();

// Test first 10 registered diagnostics
$diagnostics_to_test = [
    'Diagnostic_Memory_Limit',
    'Diagnostic_Backup',
    'Diagnostic_Permalinks',
    'Diagnostic_Tagline',
    'Diagnostic_SSL',
    'Diagnostic_Outdated_Plugins',
    'Diagnostic_Debug_Mode',
    'Diagnostic_WordPress_Version',
    'Diagnostic_Plugin_Count',
    'Diagnostic_Inactive_Plugins',
];

echo "🧪 Testing Diagnostics\n";
echo "===========================================\n";

foreach ($diagnostics_to_test as $diagnostic) {
    $tester->test_diagnostic($diagnostic);
}

$tester->get_summary();
