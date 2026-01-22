<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: AMP Support
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-accelerated-mobile-pages
 * Training: https://wpshadow.com/training/design-accelerated-mobile-pages
 */
class Diagnostic_Design_ACCELERATED_MOBILE_PAGES extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-accelerated-mobile-pages',
            'title' => __('AMP Support', 'wpshadow'),
            'description' => __('If using AMP, validates AMP styling correct.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-accelerated-mobile-pages',
            'training_link' => 'https://wpshadow.com/training/design-accelerated-mobile-pages',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
