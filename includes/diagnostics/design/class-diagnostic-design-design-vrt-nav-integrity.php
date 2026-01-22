<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: VRT Nav Integrity
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-vrt-nav-integrity
 * Training: https://wpshadow.com/training/design-vrt-nav-integrity
 */
class Diagnostic_Design_DESIGN_VRT_NAV_INTEGRITY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-vrt-nav-integrity',
            'title' => __('VRT Nav Integrity', 'wpshadow'),
            'description' => __('Detects navigation alignment, spacing, and breakpoint regressions.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-vrt-nav-integrity',
            'training_link' => 'https://wpshadow.com/training/design-vrt-nav-integrity',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
