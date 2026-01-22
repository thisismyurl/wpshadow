<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Autoloaded Options Size (DB-001)
 * 
 * Detects if autoloaded options exceed 800KB threshold.
 * Philosophy: Shows value (#9) by tracking measurable database performance improvement.
 * 
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
 */
class Diagnostic_Autoloaded_Options_Size extends Diagnostic_Base {
	
	/**
	 * Run the diagnostic check
	 * 
	 * @return array|null Array with finding details or null if no issue found
	 */
	public static function check(): ?array {
		// Check WordPress autoloaded options size
        global $wpdb;
        
        // Get total autoloaded options size
        $autoloaded_size = $wpdb->get_var(
            "SELECT COALESCE(SUM(CHAR_LENGTH(option_value)), 0) FROM {$wpdb->options} WHERE autoload='yes'"
        );
        
        // Convert to MB
        $size_mb = $autoloaded_size / (1024 * 1024);
        
        // If more than 1MB of autoloaded options, that's excessive
        if ($size_mb > 1) {
            return array(
                'id' => 'autoloaded-options-size',
                'title' => sprintf(__('Large Autoloaded Options (%s MB)', 'wpshadow'), number_format($size_mb, 2)),
                'description' => __('Autoloaded options are loaded on every page load. Consider disabling autoload for options over 1MB total.', 'wpshadow'),
                'severity' => 'medium',
                'category' => 'performance',
                'kb_link' => 'https://wpshadow.com/kb/autoloaded-options-optimization/',
                'training_link' => 'https://wpshadow.com/training/options-autoload/',
                'auto_fixable' => false,
                'threat_level' => 55,
            );
        }
        return null;
	}
}
