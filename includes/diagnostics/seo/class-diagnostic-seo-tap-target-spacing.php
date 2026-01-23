<?php
declare(strict_types=1);
/**
 * Tap Target Spacing Diagnostic
 *
 * Philosophy: 48px minimum tap target size
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Tap_Target_Spacing extends Diagnostic_Base {
    public static function check(): ?array {
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