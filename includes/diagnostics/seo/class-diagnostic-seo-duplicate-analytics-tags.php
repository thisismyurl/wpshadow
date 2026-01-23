<?php
declare(strict_types=1);
/**
 * Duplicate Analytics Tags Diagnostic
 *
 * Philosophy: Prevent double-counting and bloat from multiple tags
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Duplicate_Analytics_Tags extends Diagnostic_Base {
    /**
     * Heuristic: multiple analytics plugins active can duplicate tags.
     *
     * @return array|null
     */
    public static function check(): ?array {
        include_once ABSPATH . 'wp-admin/includes/plugin.php';
        $plugins = [
            'google-site-kit/google-site-kit.php',
            'duracelltomi-google-tag-manager/duracelltomi-google-tag-manager-for-wordpress.php',
            'monsterinsights/google-analytics-plugin.php',
        ];
        $active = 0;
        foreach ($plugins as $plugin) {
            if (function_exists('is_plugin_active') && is_plugin_active($plugin)) {
                $active++;
            }
        }
        if ($active >= 2) {
            return [
                'id' => 'seo-duplicate-analytics-tags',
                'title' => 'Potential Duplicate Analytics Tags',
                'description' => 'Multiple analytics/GTM plugins are active. Verify tags are not duplicated to avoid double-counting and performance issues.',
                'severity' => 'medium',
                'category' => 'seo',
                'kb_link' => 'https://wpshadow.com/kb/duplicate-analytics-tags/',
                'training_link' => 'https://wpshadow.com/training/analytics-setup/',
                'auto_fixable' => false,
                'threat_level' => 40,
            ];
        }
        return null;
    }

}