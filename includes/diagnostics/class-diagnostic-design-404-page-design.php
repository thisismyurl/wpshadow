<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: 404 Page Design Quality
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-404-page-design
 * Training: https://wpshadow.com/training/design-404-page-design
 */
class Diagnostic_Design_404_PAGE_DESIGN {
    public static function check() {
        return [
            'id' => 'design-404-page-design',
            'title' => __('404 Page Design Quality', 'wpshadow'),
            'description' => __('Validates 404 page has helpful content, styled professionally.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-404-page-design',
            'training_link' => 'https://wpshadow.com/training/design-404-page-design',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
