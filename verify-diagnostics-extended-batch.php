#!/usr/bin/env php
<?php
/**
 * Extended Batch Runtime Verification Script
 * 
 * Tests additional diagnostics (beyond the 88 registered) in batches
 * to verify they are functional and ready for registration.
 * 
 * Usage: docker exec wpshadow-test php /var/www/html/wp-content/plugins/wpshadow/verify-diagnostics-extended-batch.php
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

class Extended_Batch_Verifier {
    private array $passed = [];
    private array $failed = [];
    private array $errors = [];
    private string $namespace = 'WPShadow\\Diagnostics\\';
    private int $batch_size = 50;
    
    /**
     * Get all diagnostic class files in the system
     */
    private function get_all_diagnostic_files(): array {
        $diagnostics_dir = ABSPATH . 'wp-content/plugins/wpshadow/includes/diagnostics/';
        $files = [];
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($diagnostics_dir),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && strpos($file->getFilename(), 'class-diagnostic-') === 0) {
                $files[] = $file->getPathname();
            }
        }
        
        return $files;
    }
    
    /**
     * Extract class name from file path
     */
    private function get_class_name_from_file(string $file_path): ?string {
        $content = file_get_contents($file_path);
        if (!$content) {
            return null;
        }
        
        // Look for class definition
        if (preg_match('/class\s+(\w+)\s+extends/', $content, $matches)) {
            return $matches[1];
        }
        
        return null;
    }
    
    /**
     * Test a single diagnostic class
     */
    private function test_diagnostic(string $class_name): ?array {
        $full_class = $this->namespace . $class_name;
        
        // Check if class exists
        if (!class_exists($full_class)) {
            return [
                'status' => 'failed',
                'reason' => 'Class not found'
            ];
        }
        
        try {
            // Verify class has check() method
            if (!method_exists($full_class, 'check')) {
                return [
                    'status' => 'failed',
                    'reason' => 'check() method not found'
                ];
            }
            
            // Call the check() method
            $result = call_user_func([$full_class, 'check']);
            
            // Validate return type
            if ($result === null) {
                return [
                    'status' => 'passed',
                    'result' => 'null'
                ];
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
                    return [
                        'status' => 'failed',
                        'reason' => 'Missing fields: ' . implode(', ', $missing)
                    ];
                }
                
                return [
                    'status' => 'passed',
                    'result' => 'array'
                ];
            } else {
                return [
                    'status' => 'failed',
                    'reason' => 'Invalid return type: ' . gettype($result)
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => 'failed',
                'reason' => 'Exception: ' . $e->getMessage()
            ];
        } catch (Error $e) {
            return [
                'status' => 'failed',
                'reason' => 'Error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Run batched verification
     */
    public function run_batch_verification(): void {
        $files = $this->get_all_diagnostic_files();
        $total_files = count($files);
        
        echo "\n";
        echo "🧪 Extended Batch Diagnostic Verification\n";
        echo "═════════════════════════════════════════\n";
        echo "Total diagnostic files found: $total_files\n";
        echo "Batch size: {$this->batch_size}\n";
        echo "═════════════════════════════════════════\n\n";
        
        // Get registered diagnostics to skip
        $registered = $this->get_registered_diagnostics();
        
        $batch_number = 1;
        $current_batch = [];
        $tested_count = 0;
        
        foreach ($files as $file) {
            $class_name = $this->get_class_name_from_file($file);
            
            if (!$class_name) {
                continue;
            }
            
            // Skip already registered diagnostics
            if (in_array($class_name, $registered, true)) {
                continue;
            }
            
            $current_batch[$class_name] = $file;
            $tested_count++;
            
            // Process batch when it reaches batch_size
            if (count($current_batch) >= $this->batch_size) {
                $this->process_batch($batch_number, $current_batch);
                $batch_number++;
                $current_batch = [];
            }
        }
        
        // Process remaining batch
        if (!empty($current_batch)) {
            $this->process_batch($batch_number, $current_batch);
        }
        
        $this->print_summary($tested_count);
    }
    
    /**
     * Process a single batch
     */
    private function process_batch(int $batch_number, array $batch): void {
        echo "📦 Batch $batch_number (" . count($batch) . " diagnostics)\n";
        
        foreach ($batch as $class_name => $file) {
            $result = $this->test_diagnostic($class_name);
            
            if ($result === null) {
                continue;
            }
            
            if ($result['status'] === 'passed') {
                $this->passed[$class_name] = $result;
                echo "  ✅ $class_name\n";
            } else {
                $this->failed[$class_name] = $result['reason'];
                echo "  ❌ $class_name - {$result['reason']}\n";
            }
        }
        
        echo "\n";
    }
    
    /**
     * Get list of already registered diagnostics
     */
    private function get_registered_diagnostics(): array {
        // These are from the Diagnostic_Registry quick_diagnostics and deep_diagnostics arrays
        return [
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
            // Deep scan diagnostics
            'Diagnostic_Database_Health',
            'Diagnostic_Broken_Links',
        ];
    }
    
    /**
     * Print summary
     */
    private function print_summary(int $total_tested): void {
        $passed = count($this->passed);
        $failed = count($this->failed);
        $pass_rate = $total_tested > 0 ? round(($passed / $total_tested) * 100, 2) : 0;
        
        echo "\n";
        echo "╔═══════════════════════════════════════╗\n";
        echo "║       EXTENDED BATCH SUMMARY          ║\n";
        echo "╠═══════════════════════════════════════╣\n";
        printf("║ ✅ Passed: %3d / %3d (%.1f%%)        ║\n", $passed, $total_tested, $pass_rate);
        printf("║ ❌ Failed: %3d / %3d (%.1f%%)        ║\n", $failed, $total_tested, 100 - $pass_rate);
        echo "╚═══════════════════════════════════════╝\n";
        
        if ($failed > 0 && $failed <= 20) {
            echo "\n⚠️ Failed Diagnostics:\n";
            foreach ($this->failed as $class_name => $reason) {
                echo "  - $class_name: $reason\n";
            }
        } elseif ($failed > 20) {
            echo "\n⚠️ First 20 Failed Diagnostics:\n";
            $count = 0;
            foreach ($this->failed as $class_name => $reason) {
                if ($count >= 20) {
                    echo "  ... and " . ($failed - 20) . " more\n";
                    break;
                }
                echo "  - $class_name: $reason\n";
                $count++;
            }
        }
    }
}

// Run verification
$verifier = new Extended_Batch_Verifier();
$verifier->run_batch_verification();
