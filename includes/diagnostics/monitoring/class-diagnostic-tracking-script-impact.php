<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Tracking Script Performance Impact (THIRD-011)
 * 
 * Measures how analytics/tracking scripts affect page performance.
 * Philosophy: Show value (#9) - Balance tracking needs with performance.
 * 
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
 */
class Diagnostic_Tracking_Script_Impact extends Diagnostic_Base {
    
    /**
     * Run the diagnostic check
     * 
     * @return array|null Array with finding details or null if no issue found
     */
    public static function check(): ?array {
		// Monitor tracking script performance impact
        $tracking_impact = get_transient('wpshadow_tracking_script_impact_ms');
        
        if ($tracking_impact && $tracking_impact > 200) { // 200ms
            return array(
                'id' => 'tracking-script-impact',
                'title' => sprintf(__('Tracking Scripts Impact: +%dms', 'wpshadow'), $tracking_impact),
                'description' => __('Analytics and tracking scripts are adding significant overhead. Load them asynchronously to reduce impact.', 'wpshadow'),
                'severity' => 'medium',
                'category' => 'monitoring',
                'kb_link' => 'https://wpshadow.com/kb/tracking-script-optimization/',
                'training_link' => 'https://wpshadow.com/training/async-tracking-scripts/',
                'auto_fixable' => false,
                'threat_level' => 45,
            );
        }
        return null;
	}
}
