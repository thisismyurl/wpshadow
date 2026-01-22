<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Z-Index Management
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-system-z-index-management
 * Training: https://wpshadow.com/training/design-system-z-index-management
 */
class Diagnostic_Design_SYSTEM_Z_INDEX_MANAGEMENT {
    public static function check() {
        return [
            'id' => 'design-system-z-index-management',
            'title' => __('Z-Index Management', 'wpshadow'),
            'description' => __('Verifies z-index values use consistent scale, no conflicts.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-system-z-index-management',
            'training_link' => 'https://wpshadow.com/training/design-system-z-index-management',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
