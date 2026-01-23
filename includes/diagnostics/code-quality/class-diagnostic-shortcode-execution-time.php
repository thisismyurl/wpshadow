<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Shortcode Execution Time (WP-ADV-004)
 * 
 * Shortcode Execution Time diagnostic
 * Philosophy: Show value (#9) - Fast shortcodes.
 * 
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_DiagnosticShortcodeExecutionTime extends Diagnostic_Base {
    public static function check(): ?array {
        $slow_shortcodes = get_transient('wpshadow_slow_shortcodes');
        
        if ($slow_shortcodes && is_array($slow_shortcodes)) {
            $slow_count = count($slow_shortcodes);
            if ($slow_count > 0) {
                $slowest = array_key_first($slow_shortcodes);
                $slowest_time = $slow_shortcodes[$slowest];
                
                return array(
                    'id' => 'shortcode-execution-time',
                    'title' => sprintf(__('%d Slow Shortcodes Detected', 'wpshadow'), $slow_count),
                    'description' => sprintf(__('Shortcode "%s" took %dms. Optimize or lazy-load slow shortcodes for better performance.', 'wpshadow'), $slowest, $slowest_time),
                    'severity' => 'medium',
                    'category' => 'performance',
                    'kb_link' => 'https://wpshadow.com/kb/shortcode-optimization/',
                    'training_link' => 'https://wpshadow.com/training/shortcode-performance/',
                    'auto_fixable' => false,
                    'threat_level' => 45,
                );
            }
        }
        return null;
    }

}