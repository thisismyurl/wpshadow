<?php declare(strict_types=1);
/**
 * Tap Target Spacing Diagnostic
 *
 * Philosophy: 48px minimum tap target size
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Tap_Target_Spacing {
    public static function check() {
        return [
            'id' => 'seo-tap-target-spacing',
            'title' => 'Touch Target Size and Spacing',
            'description' => 'Touch targets should be 48x48px minimum with adequate spacing to prevent mis-taps.',
            'severity' => 'medium',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/tap-targets/',
            'training_link' => 'https://wpshadow.com/training/mobile-accessibility/',
            'auto_fixable' => false,
            'threat_level' => 35,
        ];
    }
}
