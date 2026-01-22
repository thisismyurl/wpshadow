<?php declare(strict_types=1);
/**
 * Plugin Conflict Detection Diagnostic
 *
 * Philosophy: One plugin per job reduces conflicts
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Plugin_Conflict_Detection {
    public static function check() {
        $cache_plugins = ['wp-super-cache', 'w3-total-cache', 'wp-rocket', 'wp-fastest-cache'];
        $seo_plugins = ['wordpress-seo', 'all-in-one-seo-pack', 'seo-by-rank-math'];
        $active = 0;
        include_once ABSPATH . 'wp-admin/includes/plugin.php';
        foreach (array_merge($cache_plugins, $seo_plugins) as $plugin) {
            if (function_exists('is_plugin_active') && is_plugin_active($plugin . '/' . $plugin . '.php')) {
                $active++;
            }
        }
        if ($active > 2) {
            return [
                'id' => 'seo-plugin-conflict-detection',
                'title' => 'Potential Plugin Conflicts',
                'description' => 'Multiple plugins detected performing similar functions. This can cause conflicts and performance issues.',
                'severity' => 'medium',
                'category' => 'seo',
                'kb_link' => 'https://wpshadow.com/kb/plugin-conflicts/',
                'training_link' => 'https://wpshadow.com/training/plugin-management/',
                'auto_fixable' => false,
                'threat_level' => 40,
            ];
        }
        return null;
    }
}
