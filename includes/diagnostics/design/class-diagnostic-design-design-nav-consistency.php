<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Nav Consistency
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-nav-consistency
 * Training: https://wpshadow.com/training/design-nav-consistency
 */
class Diagnostic_Design_DESIGN_NAV_CONSISTENCY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-nav-consistency',
            'title' => __('Nav Consistency', 'wpshadow'),
            'description' => __('Checks nav spacing, states, and dropdown alignment consistency.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-nav-consistency',
            'training_link' => 'https://wpshadow.com/training/design-nav-consistency',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}