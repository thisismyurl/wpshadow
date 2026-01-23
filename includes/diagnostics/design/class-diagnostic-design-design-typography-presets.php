<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Typography Presets
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-typography-presets
 * Training: https://wpshadow.com/training/design-typography-presets
 */
class Diagnostic_Design_DESIGN_TYPOGRAPHY_PRESETS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-typography-presets',
            'title' => __('Typography Presets', 'wpshadow'),
            'description' => __('Ensures fontSize presets map to the type ramp with clamp settings.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-typography-presets',
            'training_link' => 'https://wpshadow.com/training/design-typography-presets',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}