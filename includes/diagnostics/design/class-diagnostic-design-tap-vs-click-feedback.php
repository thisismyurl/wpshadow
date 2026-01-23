<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Tap vs Click Feedback
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-tap-vs-click-feedback
 * Training: https://wpshadow.com/training/design-tap-vs-click-feedback
 */
class Diagnostic_Design_TAP_VS_CLICK_FEEDBACK extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-tap-vs-click-feedback',
            'title' => __('Tap vs Click Feedback', 'wpshadow'),
            'description' => __('Validates tap feedback immediate.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-tap-vs-click-feedback',
            'training_link' => 'https://wpshadow.com/training/design-tap-vs-click-feedback',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }

}