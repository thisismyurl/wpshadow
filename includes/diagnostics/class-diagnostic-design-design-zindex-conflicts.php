<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Z-Index Conflicts
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-zindex-conflicts
 * Training: https://wpshadow.com/training/design-zindex-conflicts
 */
class Diagnostic_Design_DESIGN_ZINDEX_CONFLICTS {
    public static function check() {
        return [
            'id' => 'design-zindex-conflicts',
            'title' => __('Z-Index Conflicts', 'wpshadow'),
            'description' => __('Detects overlapping or conflicting z-index values.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-zindex-conflicts',
            'training_link' => 'https://wpshadow.com/training/design-zindex-conflicts',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

