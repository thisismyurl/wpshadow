<?php declare(strict_types=1);
/**
 * Geo/IP Redirects Diagnostic
 *
 * Philosophy: Avoid crawl-blocking language/location auto-redirects
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Geo_Redirects {
    /**
     * Heuristic: flag common plugins that auto-redirect by locale.
     *
     * @return array|null
     */
    public static function check() {
        include_once ABSPATH . 'wp-admin/includes/plugin.php';
        $plugins = [
            'sitepress-multilingual-cms/sitepress.php', // WPML
            'translatepress-multilingual/translatepress-multilingual.php',
        ];
        foreach ($plugins as $plugin) {
            if (function_exists('is_plugin_active') && is_plugin_active($plugin)) {
                return [
                    'id' => 'seo-geo-redirects',
                    'title' => 'Potential Geo/Language Auto-Redirects',
                    'description' => 'Language or geo-based auto-redirects can hinder crawling. Ensure bots can access canonical versions without forced redirects.',
                    'severity' => 'medium',
                    'category' => 'seo',
                    'kb_link' => 'https://wpshadow.com/kb/geo-redirects-seo/',
                    'training_link' => 'https://wpshadow.com/training/international-redirects/',
                    'auto_fixable' => false,
                    'threat_level' => 45,
                ];
            }
        }
        return null;
    }
}
