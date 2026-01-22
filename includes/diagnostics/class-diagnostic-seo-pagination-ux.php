<?php declare(strict_types=1);
/**
 * Pagination UX Diagnostic
 *
 * Philosophy: Clear next/prev links for crawlers
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Pagination_UX {
    public static function check() {
        return [
            'id' => 'seo-pagination-ux',
            'title' => 'Pagination UX and Crawlability',
            'description' => 'Ensure pagination has clear next/prev links with crawlable anchor tags.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/pagination-seo/',
            'training_link' => 'https://wpshadow.com/training/site-architecture/',
            'auto_fixable' => false,
            'threat_level' => 15,
        ];
    }
}
