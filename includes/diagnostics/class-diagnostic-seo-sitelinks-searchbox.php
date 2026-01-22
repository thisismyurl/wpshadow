<?php declare(strict_types=1);
/**
 * Sitelinks Search Box Diagnostic
 *
 * Philosophy: Enhance SERP features with schema
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Sitelinks_SearchBox {
    /**
     * Advisory: ensure WebSite schema with SearchAction is present.
     *
     * @return array|null
     */
    public static function check() {
        return [
            'id' => 'seo-sitelinks-searchbox',
            'title' => 'Sitelinks Search Box Schema',
            'description' => 'Add WebSite structured data with potentialAction SearchAction to enable sitelinks search box in SERPs.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/sitelinks-search-box/',
            'training_link' => 'https://wpshadow.com/training/schema-serp-features/',
            'auto_fixable' => false,
            'threat_level' => 15,
        ];
    }
}
