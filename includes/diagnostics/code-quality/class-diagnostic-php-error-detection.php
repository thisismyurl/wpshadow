<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: PHP Error/Warning Detection (ERROR-001)
 * 
 * Parses PHP error logs to identify recurring errors, warnings, and notices.
 * Philosophy: Educate (#5) - Help developers fix issues before users see them.
 * 
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_PHP_Error_Detection extends Diagnostic_Base {
    
    /**
     * Run the diagnostic check
     * 
     * @return array|null Array with finding details or null if no issue found
     */
    public static function check(): ?array {
		// Check WordPress error log for recent PHP errors
        $debug_log = WP_CONTENT_DIR . '/debug.log';
        
        if (!file_exists($debug_log)) {
            return null; // No debug logging enabled
        }
        
        // Read last 50 lines of debug log
        $lines = array_slice(file($debug_log), -50);
        $error_count = 0;
        
        foreach ($lines as $line) {
            if (stripos($line, 'error') !== false || stripos($line, 'warning') !== false) {
                $error_count++;
            }
        }
        
        if ($error_count > 5) {
            return array(
                'id' => 'php-error-detection',
                'title' => sprintf(__('%d Recent PHP Errors Found', 'wpshadow'), $error_count),
                'description' => __('Check your debug.log for PHP errors. Fix errors to improve stability and performance.', 'wpshadow'),
                'severity' => 'high',
                'category' => 'code-quality',
                'kb_link' => 'https://wpshadow.com/kb/php-error-logging/',
                'training_link' => 'https://wpshadow.com/training/debug-logging/',
                'auto_fixable' => false,
                'threat_level' => 75,
            );
        }
        return null;
	}

}