<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Header Sticky Behavior Design
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-header-sticky-behavior
 * Training: https://wpshadow.com/training/design-header-sticky-behavior
 */
class Diagnostic_Design_HEADER_STICKY_BEHAVIOR {
    public static function check() {
        return [
            'id' => 'design-header-sticky-behavior',
            'title' => __('Header Sticky Behavior Design', 'wpshadow'),
            'description' => __('Verifies sticky header doesn't obscure content, maintains readability.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-header-sticky-behavior',
            'training_link' => 'https://wpshadow.com/training/design-header-sticky-behavior',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
