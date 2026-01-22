<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Spacing Scale Adherence
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-spacing-scale-adherence
 * Training: https://wpshadow.com/training/design-spacing-scale-adherence
 */
class Diagnostic_Design_DESIGN_SPACING_SCALE_ADHERENCE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-spacing-scale-adherence',
            'title' => __('Spacing Scale Adherence', 'wpshadow'),
            'description' => __('Flags margins and paddings that are off the spacing scale.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-spacing-scale-adherence',
            'training_link' => 'https://wpshadow.com/training/design-spacing-scale-adherence',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
