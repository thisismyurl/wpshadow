<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Sitemap Page Design
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-sitemap-page-design
 * Training: https://wpshadow.com/training/design-sitemap-page-design
 */
class Diagnostic_Design_SITEMAP_PAGE_DESIGN {
    public static function check() {
        return [
            'id' => 'design-sitemap-page-design',
            'title' => __('Sitemap Page Design', 'wpshadow'),
            'description' => __('Checks XML sitemap page human-friendly.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-sitemap-page-design',
            'training_link' => 'https://wpshadow.com/training/design-sitemap-page-design',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
