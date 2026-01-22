<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: REST API Content Formatting
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-rest-api-content-display
 * Training: https://wpshadow.com/training/design-rest-api-content-display
 */
class Diagnostic_Design_REST_API_CONTENT_DISPLAY {
    public static function check() {
        return [
            'id' => 'design-rest-api-content-display',
            'title' => __('REST API Content Formatting', 'wpshadow'),
            'description' => __('Verifies REST API content renders properly on front-end.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-rest-api-content-display',
            'training_link' => 'https://wpshadow.com/training/design-rest-api-content-display',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
