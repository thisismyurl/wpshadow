<?php declare(strict_types=1);
/**
 * Discourage Search Engines (blog_public) Diagnostic
 *
 * Philosophy: Technical SEO visibility control
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Discourage_Search_Engines {
    /**
     * Check if the site is set to discourage search engines.
     *
     * @return array|null
     */
    public static function check() {
        $blog_public = get_option('blog_public');
        if ($blog_public === '0' || $blog_public === 0) {
            return [
                'id' => 'seo-discourage-search-engines',
                'title' => 'Search Engine Visibility Disabled',
                'description' => 'WordPress is set to discourage search engines (noindex). Disable this in Settings → Reading for production sites.',
                'severity' => 'high',
                'category' => 'seo',
                'kb_link' => 'https://wpshadow.com/kb/search-engine-visibility/',
                'training_link' => 'https://wpshadow.com/training/indexation-basics/',
                'auto_fixable' => false,
                'threat_level' => 80,
            ];
        }
        return null;
    }
}
