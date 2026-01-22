<?php
declare(strict_types=1);
/**
 * Schema Conflicts Diagnostic
 *
 * Philosophy: Avoid duplicate/contradictory schema outputs
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Schema_Conflicts extends Diagnostic_Base {
    /**
     * Heuristic: multiple schema/SEO plugins active can output conflicting markup.
     *
     * @return array|null
     */
    public static function check(): ?array {
        include_once ABSPATH . 'wp-admin/includes/plugin.php';
        $conflictPlugins = [
            'wordpress-seo/wp-seo.php',
            'wp-seopress/seopress.php',
            'schema-and-structured-data-for-wp/schema-and-structured-data-for-wp.php',
        ];
        $active = 0;
        foreach ($conflictPlugins as $plugin) {
            if (function_exists('is_plugin_active') && is_plugin_active($plugin)) {
                $active++;
            }
        }
        if ($active >= 2) {
            return [
                'id' => 'seo-schema-conflicts',
                'title' => 'Potential Schema Output Conflicts',
                'description' => 'Multiple schema-capable plugins are active. This can duplicate or contradict structured data. Use a single source of truth.',
                'severity' => 'medium',
                'category' => 'seo',
                'kb_link' => 'https://wpshadow.com/kb/schema-conflicts/',
                'training_link' => 'https://wpshadow.com/training/structured-data/',
                'auto_fixable' => false,
                'threat_level' => 50,
            ];
        }
        return null;
    }
}
