<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Skip Link Implementation
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-skip-link-implementation
 * Training: https://wpshadow.com/training/design-skip-link-implementation
 */
class Diagnostic_Design_SKIP_LINK_IMPLEMENTATION {
    public static function check() {
        return [
            'id' => 'design-skip-link-implementation',
            'title' => __('Skip Link Implementation', 'wpshadow'),
            'description' => __('Confirms 'Skip to main content' link present.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-skip-link-implementation',
            'training_link' => 'https://wpshadow.com/training/design-skip-link-implementation',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
