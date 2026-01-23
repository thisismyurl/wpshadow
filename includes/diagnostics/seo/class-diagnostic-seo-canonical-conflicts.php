<?php
declare(strict_types=1);
/**
 * Canonical Tag Conflicts Diagnostic
 *
 * Philosophy: Prevent duplicate/conflicting canonicals
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Canonical_Conflicts extends Diagnostic_Base {
    /**
     * Heuristic: multiple SEO plugins active often cause duplicate canonicals.
     *
     * @return array|null
     */
    public static function check(): ?array {
        include_once ABSPATH . 'wp-admin/includes/plugin.php';
        $active = [];
        $plugins = [
            'wordpress-seo/wp-seo.php',
            'seo-by-rank-math/rank-math.php',
            'all-in-one-seo-pack/all_in_one_seo_pack.php',
            'wp-seopress/seopress.php',
        ];
        foreach ($plugins as $plugin) {
            if (function_exists('is_plugin_active') && is_plugin_active($plugin)) {
                $active[] = $plugin;
            }
        }
        if (count($active) >= 2) {
            return [
                'id' => 'seo-canonical-conflicts',
                'title' => 'Potential Canonical Tag Conflicts',
                'description' => 'Multiple SEO plugins are active. This can output duplicate or conflicting canonical tags. Keep only one SEO plugin active.',
                'severity' => 'medium',
                'category' => 'seo',
                'kb_link' => 'https://wpshadow.com/kb/canonical-tag-conflicts/',
                'training_link' => 'https://wpshadow.com/training/canonicalization/',
                'auto_fixable' => false,
                'threat_level' => 55,
            ];
        }
        return null;
    }

}