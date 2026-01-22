<?php
declare(strict_types=1);
/**
 * Sitemap Index Partitioning Diagnostic
 *
 * Philosophy: Large sites should split sitemaps properly
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Sitemap_Index_Partitioning extends Diagnostic_Base {
    /**
     * Heuristic: check presence of sitemap index vs single sitemap.
     *
     * @return array|null
     */
    public static function check(): ?array {
        $index = wp_remote_head(home_url('/sitemap-index.xml'), ['timeout' => 3]);
        if (!is_wp_error($index)) {
            $code = wp_remote_retrieve_response_code($index);
            if ($code >= 200 && $code < 400) {
                return null; // Index exists
            }
        }
        $single = wp_remote_head(home_url('/sitemap.xml'), ['timeout' => 3]);
        if (!is_wp_error($single)) {
            $code = wp_remote_retrieve_response_code($single);
            if ($code >= 200 && $code < 400) {
                return [
                    'id' => 'seo-sitemap-index-partitioning',
                    'title' => 'Single Sitemap Detected (Consider Index)',
                    'description' => 'A single sitemap was detected. Large sites benefit from sitemap indexes that partition URLs into multiple files.',
                    'severity' => 'low',
                    'category' => 'seo',
                    'kb_link' => 'https://wpshadow.com/kb/sitemap-index-best-practices/',
                    'training_link' => 'https://wpshadow.com/training/sitemaps-at-scale/',
                    'auto_fixable' => false,
                    'threat_level' => 20,
                ];
            }
        }
        return null;
    }
}
