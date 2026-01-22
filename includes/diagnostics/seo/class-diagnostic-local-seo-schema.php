<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Local Business Schema Markup
 * 
 * Target Persona: Local Business Owner (Bakery/Plumber/Insurance)
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Local_SEO_Schema extends Diagnostic_Base {
    protected static $slug = 'local-seo-schema';
    protected static $title = 'Local Business Schema Markup';
    protected static $description = 'Checks for LocalBusiness schema to appear in Google.';

    public static function check(): ?array {
        // Check for schema plugins
        $schema_plugins = array(
            'schema-and-structured-data-for-wp/structured-data-for-wp.php',
            'all-in-one-seo-pack/all_in_one_seo_pack.php',
            'wordpress-seo/wp-seo.php',
        );
        
        foreach ($schema_plugins as $plugin) {
            if (is_plugin_active($plugin)) {
                return null; // Pass - schema plugin active
            }
        }
        
        // Check for LocalBusiness schema in page source
        ob_start();
        wp_head();
        $head = ob_get_clean();
        
        if (strpos($head, 'LocalBusiness') !== false || strpos($head, 'schema.org/LocalBusiness') !== false) {
            return null; // Pass - LocalBusiness schema present
        }
        
        return array(
            'id'            => static::$slug,
            'title'         => static::$title,
            'description'   => 'No LocalBusiness schema markup detected.',
            'color'         => '#ff9800',
            'bg_color'      => '#fff3e0',
            'kb_link'       => 'https://wpshadow.com/kb/local-seo-schema/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=local-seo-schema',
            'training_link' => 'https://wpshadow.com/training/local-seo-schema/',
            'auto_fixable'  => false,
            'threat_level'  => 60,
            'module'        => 'SEO',
            'priority'      => 1,
        );
    }
}
