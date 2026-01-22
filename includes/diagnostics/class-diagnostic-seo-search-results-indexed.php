<?php declare(strict_types=1);
/**
 * Indexed Search Results Diagnostic
 *
 * Philosophy: Avoid indexing internal search pages
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Search_Results_Indexed {
    /**
     * Advisory: ensure internal search results are noindex
     *
     * @return array|null
     */
    public static function check() {
        return [
            'id' => 'seo-search-results-indexed',
            'title' => 'Internal Search Results Should Be Noindex',
            'description' => 'Ensure internal search result pages (/?s=) are set to noindex to prevent low-value pages from being indexed.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/noindex-internal-search/',
            'training_link' => 'https://wpshadow.com/training/indexation-controls/',
            'auto_fixable' => false,
            'threat_level' => 25,
        ];
    }
}
