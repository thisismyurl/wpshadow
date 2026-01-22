<?php declare(strict_types=1);
/**
 * Site Search Quality Diagnostic
 *
 * Philosophy: Good search keeps users engaged
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Site_Search_Quality {
    public static function check() {
        return [
            'id' => 'seo-site-search-quality',
            'title' => 'Site Search Functionality',
            'description' => 'Optimize site search: relevance ranking, filters, autocomplete, search analytics.',
            'severity' => 'medium',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/site-search/',
            'training_link' => 'https://wpshadow.com/training/search-optimization/',
            'auto_fixable' => false,
            'threat_level' => 35,
        ];
    }
}
