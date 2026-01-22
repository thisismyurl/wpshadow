<?php declare(strict_types=1);
/**
 * Infinite Scroll SEO Diagnostic
 *
 * Philosophy: Infinite scroll needs pagination fallback
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Infinite_Scroll_SEO {
    public static function check() {
        return [
            'id' => 'seo-infinite-scroll-seo',
            'title' => 'Infinite Scroll Pagination Strategy',
            'description' => 'Implement "View More" or pagination fallback for infinite scroll to ensure content is crawlable.',
            'severity' => 'medium',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/infinite-scroll-seo/',
            'training_link' => 'https://wpshadow.com/training/pagination-best-practices/',
            'auto_fixable' => false,
            'threat_level' => 45,
        ];
    }
}
