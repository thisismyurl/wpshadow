<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Typography Ramp Adherence
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-typography-ramp-adherence
 * Training: https://wpshadow.com/training/design-typography-ramp-adherence
 */
class Diagnostic_Design_DESIGN_TYPOGRAPHY_RAMP_ADHERENCE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-typography-ramp-adherence',
            'title' => __('Typography Ramp Adherence', 'wpshadow'),
            'description' => __('Flags font sizes that do not align to the type ramp.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-typography-ramp-adherence',
            'training_link' => 'https://wpshadow.com/training/design-typography-ramp-adherence',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
