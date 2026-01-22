<?php
declare(strict_types=1);
/**
 * Taxonomy Sitemaps Diagnostic
 *
 * Philosophy: Ensure categories/tags are appropriately included
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Taxonomy_Sitemaps extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-taxonomy-sitemaps',
            'title' => 'Taxonomy Sitemaps Coverage',
            'description' => 'Verify category and tag sitemaps are present and sized reasonably for crawl efficiency.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/taxonomy-sitemaps/',
            'training_link' => 'https://wpshadow.com/training/sitemap-optimization/',
            'auto_fixable' => false,
            'threat_level' => 15,
        ];
    }
}
