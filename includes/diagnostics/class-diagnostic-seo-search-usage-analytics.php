<?php declare(strict_types=1);
/**
 * Search Usage Analytics Diagnostic
 *
 * Philosophy: Search queries reveal user intent
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Search_Usage_Analytics {
    public static function check() {
        return [
            'id' => 'seo-search-usage-analytics',
            'title' => 'Site Search Analytics',
            'description' => 'Analyze site search queries to understand user needs and content gaps.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/search-analytics/',
            'training_link' => 'https://wpshadow.com/training/user-intent/',
            'auto_fixable' => false,
            'threat_level' => 20,
        ];
    }
}
