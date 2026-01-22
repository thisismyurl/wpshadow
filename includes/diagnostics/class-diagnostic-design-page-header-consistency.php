<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Page Header Consistency
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-page-header-consistency
 * Training: https://wpshadow.com/training/design-page-header-consistency
 */
class Diagnostic_Design_PAGE_HEADER_CONSISTENCY {
    public static function check() {
        return [
            'id' => 'design-page-header-consistency',
            'title' => __('Page Header Consistency', 'wpshadow'),
            'description' => __('Confirms page headers styled consistently across post types.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-page-header-consistency',
            'training_link' => 'https://wpshadow.com/training/design-page-header-consistency',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
