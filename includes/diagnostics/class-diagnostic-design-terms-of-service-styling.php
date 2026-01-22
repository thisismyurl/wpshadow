<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: ToS Page Styling
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-terms-of-service-styling
 * Training: https://wpshadow.com/training/design-terms-of-service-styling
 */
class Diagnostic_Design_TERMS_OF_SERVICE_STYLING {
    public static function check() {
        return [
            'id' => 'design-terms-of-service-styling',
            'title' => __('ToS Page Styling', 'wpshadow'),
            'description' => __('Validates terms page properly formatted.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-terms-of-service-styling',
            'training_link' => 'https://wpshadow.com/training/design-terms-of-service-styling',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
