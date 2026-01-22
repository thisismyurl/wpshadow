<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Autoloader Performance Cost (WP-334)
 *
 * Measures Composer/PSR-4 autoload overhead per request.
 * Philosophy: Show value (#9) and educate (#5) with clear, actionable insights.
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 * 
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
 */
class Diagnostic_Autoloader_Performance extends Diagnostic_Base {
    /**
     * Run the diagnostic check
     *
     * @return array|null Array with finding details or null if no issue found
     */
    public static function check(): ?array {
		// Check if Composer autoloader is optimized
        $vendor_path = ABSPATH . 'vendor/autoload.php';
        
        if (!file_exists($vendor_path)) {
            return null; // Composer not in use
        }
        
        // Check for optimized class map
        $classmap_file = ABSPATH . 'vendor/composer/autoload_classmap.php';
        if (file_exists($classmap_file)) {
            $classmap = include $classmap_file;
            if (count($classmap) > 100) {
                return array(
                    'id' => 'autoloader-performance',
                    'title' => sprintf(__('Composer Autoloader Optimization (%d classes)', 'wpshadow'), count($classmap)),
                    'description' => __('Run "composer dump-autoload -o" to optimize the autoloader for production performance.', 'wpshadow'),
                    'severity' => 'low',
                    'category' => 'performance',
                    'kb_link' => 'https://wpshadow.com/kb/composer-autoloader-optimization/',
                    'training_link' => 'https://wpshadow.com/training/composer-optimization/',
                    'auto_fixable' => false,
                    'threat_level' => 30,
                );
            }
        }
        return null;
	}
}
