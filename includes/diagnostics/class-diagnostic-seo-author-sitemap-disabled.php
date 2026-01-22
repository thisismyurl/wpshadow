<?php declare(strict_types=1);
/**
 * Author Sitemap Disabled Diagnostic
 *
 * Philosophy: Avoid low-value author archives on single-author sites
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Author_Sitemap_Disabled {
    public static function check() {
        return [
            'id' => 'seo-author-sitemap-disabled',
            'title' => 'Disable Author Sitemap on Single-Author Sites',
            'description' => 'Single-author sites should consider disabling author archive sitemaps to reduce low-value indexation.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/author-archives-seo/',
            'training_link' => 'https://wpshadow.com/training/archive-templates-seo/',
            'auto_fixable' => false,
            'threat_level' => 10,
        ];
    }
}
