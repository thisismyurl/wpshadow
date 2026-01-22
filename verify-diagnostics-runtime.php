#!/usr/bin/env php
<?php
/**
 * Runtime Functional Verification Script
 * 
 * Tests each registered diagnostic to ensure:
 * 1. Class loads without errors
 * 2. check() method executes without exceptions
 * 3. Return type is correct (null or array)
 * 4. Array returns have required fields
 * 
 * Run: php verify-diagnostics-runtime.php
 */

declare(strict_types=1);

// WordPress bootstrap
if (!defined('ABSPATH')) {
    define('ABSPATH', '/var/www/html/');
    if (!file_exists(ABSPATH . 'wp-load.php')) {
        die("❌ WordPress not found at {$ABSPATH}\n");
    }
    require_once ABSPATH . 'wp-load.php';
}

class Runtime_Diagnostic_Verifier {
    private array $results = [];
    private string $current_diagnostic = '';
    private int $passed = 0;
    private int $failed = 0;
    private string $log_file = '/workspaces/wpshadow/DIAGNOSTIC_RUNTIME_VERIFICATION.log';
    
    public function __construct() {
        // Clear log file
        file_put_contents($this->log_file, "=== Runtime Diagnostic Verification ===\n");
        file_put_contents($this->log_file, "Started: " . date('Y-m-d H:i:s') . "\n\n", FILE_APPEND);
    }
    
    /**
     * Test a single diagnostic
     */
    public function test_diagnostic(string $class_name): bool {
        $this->current_diagnostic = $class_name;
        $full_class = 'WPShadow\\Diagnostics\\' . $class_name;
        
        // Check if class exists
        if (!class_exists($full_class)) {
            $this->log_fail("Class not found");
            return false;
        }
        
        try {
            // Verify class has check() method
            if (!method_exists($full_class, 'check')) {
                $this->log_fail("check() method not found");
                return false;
            }
            
            // Call the check() method
            $result = call_user_func([$full_class, 'check']);
            
            // Validate return type
            if ($result === null) {
                $this->log_pass("Passed (no issues)");
                return true;
            } elseif (is_array($result)) {
                // Verify required fields
                $required = ['id', 'title', 'description'];
                $missing = [];
                foreach ($required as $field) {
                    if (!isset($result[$field])) {
                        $missing[] = $field;
                    }
                }
                
                if (!empty($missing)) {
                    $this->log_fail("Missing required fields: " . implode(', ', $missing));
                    return false;
                }
                
                $this->log_pass("Issue detected with all required fields");
                return true;
            } else {
                $this->log_fail("Invalid return type: " . gettype($result));
                return false;
            }
        } catch (Exception $e) {
            $this->log_fail("Exception: " . $e->getMessage());
            return false;
        } catch (Error $e) {
            $this->log_fail("Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Log successful test
     */
    private function log_pass(string $message): void {
        $this->passed++;
        echo "  ✅ {$message}\n";
        $this->results[$this->current_diagnostic] = 'PASS';
        file_put_contents(
            $this->log_file, 
            "✅ {$this->current_diagnostic}: {$message}\n",
            FILE_APPEND
        );
    }
    
    /**
     * Log failed test
     */
    private function log_fail(string $message): void {
        $this->failed++;
        echo "  ❌ {$message}\n";
        $this->results[$this->current_diagnostic] = 'FAIL';
        file_put_contents(
            $this->log_file,
            "❌ {$this->current_diagnostic}: {$message}\n",
            FILE_APPEND
        );
    }
    
    /**
     * Get summary of results
     */
    public function print_summary(): void {
        $total = $this->passed + $this->failed;
        $pass_rate = $total > 0 ? round(($this->passed / $total) * 100, 2) : 0;
        
        echo "\n";
        echo "╔═══════════════════════════════════════╗\n";
        echo "║       VERIFICATION SUMMARY            ║\n";
        echo "╠═══════════════════════════════════════╣\n";
        printf("║ ✅ Passed: %3d / %3d (%.1f%%)        ║\n", $this->passed, $total, $pass_rate);
        printf("║ ❌ Failed: %3d / %3d (%.1f%%)        ║\n", $this->failed, $total, 100 - $pass_rate);
        echo "╠═══════════════════════════════════════╣\n";
        echo "║ Log: DIAGNOSTIC_RUNTIME_VERIFICATION.log\n";
        echo "╚═══════════════════════════════════════╝\n";
        
        // Write summary to log
        file_put_contents(
            $this->log_file,
            "\n=== SUMMARY ===\n" .
            "Passed: {$this->passed}\n" .
            "Failed: {$this->failed}\n" .
            "Total: {$total}\n" .
            "Pass Rate: {$pass_rate}%\n" .
            "Completed: " . date('Y-m-d H:i:s') . "\n",
            FILE_APPEND
        );
    }
    
    /**
     * Get failed diagnostics list
     */
    public function get_failed_diagnostics(): array {
        $failed = [];
        foreach ($this->results as $diagnostic => $status) {
            if ($status === 'FAIL') {
                $failed[] = $diagnostic;
            }
        }
        return $failed;
    }
}

// List of diagnostics registered in Diagnostic_Registry
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
    'Diagnostic_Theme_Update_Noise',
    'Diagnostic_Plugin_Update_Noise',
    'Diagnostic_Hotlink_Protection',
    'Diagnostic_Head_Cleanup_Emoji',
    'Diagnostic_Head_Cleanup_OEmbed',
    'Diagnostic_Head_Cleanup_RSD',
    'Diagnostic_Head_Cleanup_Shortlink',
    'Diagnostic_Iframe_Busting',
    'Diagnostic_Image_Lazy_Load',
    'Diagnostic_External_Fonts',
    'Diagnostic_Jquery_Migrate',
    'Diagnostic_Plugin_Auto_Updates',
    'Diagnostic_Error_Log',
    'Diagnostic_Core_Integrity',
    'Diagnostic_Skiplinks',
    'Diagnostic_Asset_Versions_CSS',
    'Diagnostic_Asset_Versions_JS',
    'Diagnostic_CSS_Classes',
    'Diagnostic_Maintenance',
    'Diagnostic_Nav_ARIA',
    'Diagnostic_Admin_Username',
    'Diagnostic_Admin_Font_Bloat',
    'Diagnostic_Admin_Theme_Assets',
    'Diagnostic_Search_Indexing',
    'Diagnostic_Admin_Email',
    'Diagnostic_User_Notification_Email',
    'Diagnostic_Timezone',
    'Diagnostic_Content_Optimizer',
    'Diagnostic_Paste_Cleanup',
    'Diagnostic_HTML_Cleanup',
    'Diagnostic_Pre_Publish_Review',
    'Diagnostic_Embed_Disable',
    'Diagnostic_Interactivity_Cleanup',
    'Diagnostic_PHP_Version',
    'Diagnostic_File_Permissions',
    'Diagnostic_Security_Headers',
    'Diagnostic_Post_Via_Email',
    'Diagnostic_Post_Via_Email_Category',
    'Diagnostic_Initial_Setup',
    'Diagnostic_Comments_Disabled',
    'Diagnostic_Howdy_Greeting',
    'Diagnostic_Dark_Mode',
    'Diagnostic_Mobile_Friendliness',
    'Diagnostic_Database_Indexes',
    'Diagnostic_PHP_Compatibility',
    'Diagnostic_Theme_Performance',
    'Diagnostic_Font_Optimization',
    'Diagnostic_Monitoring_Status',
    'Diagnostic_Backup_Verification',
    'Diagnostic_Automation_Readiness',
    'Diagnostic_Object_Cache',
    'Diagnostic_Heartbeat_Throttling',
    'Diagnostic_XML_Sitemap',
    'Diagnostic_Robots_Txt',
    'Diagnostic_Favicon',
    'Diagnostic_Two_Factor',
    'Diagnostic_Disallow_File_Edit',
    'Diagnostic_Webhooks_Readiness',
    'Diagnostic_Resource_Hints',
    'Diagnostic_REST_API',
    'Diagnostic_RSS_Feeds',
    'Diagnostic_WP_Generator',
    'Diagnostic_Block_Cleanup',
    'Diagnostic_Consent_Checks',
    'Diagnostic_Emoji_Scripts',
    'Diagnostic_JQuery_Cleanup',
];

// Run verification
echo "\n";
echo "🧪 Runtime Functional Verification\n";
echo "═════════════════════════════════════\n";
echo "Testing " . count($diagnostics_to_test) . " registered diagnostics\n";
echo "═════════════════════════════════════\n\n";

$verifier = new Runtime_Diagnostic_Verifier();

foreach ($diagnostics_to_test as $diagnostic) {
    echo "Testing: $diagnostic\n";
    $verifier->test_diagnostic($diagnostic);
}

$verifier->print_summary();

// List any failed diagnostics
$failed = $verifier->get_failed_diagnostics();
if (!empty($failed)) {
    echo "\n⚠️ Failed Diagnostics (need investigation):\n";
    foreach ($failed as $f) {
        echo "  - $f\n";
    }
}
