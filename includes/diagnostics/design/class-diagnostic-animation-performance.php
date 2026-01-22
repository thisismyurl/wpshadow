<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Animation Performance (FE-018)
 * 
 * Measures animation frame rate smoothness (60fps target).
 * Philosophy: Show value (#9) - Buttery smooth animations.
 * 
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
 */
class Diagnostic_Animation_Performance extends Diagnostic_Base {
    public static function check(): ?array {
        // Monitor animation performance impact
        $animation_impact = get_transient('wpshadow_animation_perf_impact_ms');
        
        if ($animation_impact && $animation_impact > 100) { // 100ms
            return array(
                'id' => 'animation-performance',
                'title' => sprintf(__('Animations Adding +%dms Overhead', 'wpshadow'), $animation_impact),
                'description' => __('CSS animations are adding noticeable performance overhead. Use will-change, GPU acceleration, and limit simultaneous animations.', 'wpshadow'),
                'severity' => 'low',
                'category' => 'design',
                'kb_link' => 'https://wpshadow.com/kb/animation-optimization/',
                'training_link' => 'https://wpshadow.com/training/css-animations/',
                'auto_fixable' => false,
                'threat_level' => 35,
            );
        }
        return null;
    }
}
