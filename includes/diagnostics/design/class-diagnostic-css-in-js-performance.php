<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: CSS-in-JS Performance Impact (ASSET-ADV-002)
 * 
 * CSS-in-JS Performance Impact diagnostic
 * Philosophy: Educate (#5) - CSS-in-JS trade-offs.
 * 
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
 */
class Diagnostic_DiagnosticCssInJsPerformance extends Diagnostic_Base {
    public static function check(): ?array {
        // Check for CSS-in-JS performance impact
        $css_js_overhead = get_transient('wpshadow_css_in_js_overhead_ms');
        
        if ($css_js_overhead && $css_js_overhead > 50) { // 50ms
            return array(
                'id' => 'css-in-js-performance',
                'title' => sprintf(__('CSS-in-JS Adding +%dms', 'wpshadow'), $css_js_overhead),
                'description' => __('CSS-in-JS libraries can add runtime overhead. Consider pure CSS or lightweight alternatives for better performance.', 'wpshadow'),
                'severity' => 'low',
                'category' => 'design',
                'kb_link' => 'https://wpshadow.com/kb/css-in-js-alternatives/',
                'training_link' => 'https://wpshadow.com/training/styling-strategies/',
                'auto_fixable' => false,
                'threat_level' => 35,
            );
        }
        return null;
    }
}
