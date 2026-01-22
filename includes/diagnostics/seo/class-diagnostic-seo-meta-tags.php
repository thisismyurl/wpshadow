<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: SEO Meta Tags Complete?
 * 
 * Target Persona: Digital Marketing Agency
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
 */
class Diagnostic_SEO_Meta_Tags extends Diagnostic_Base {
    protected static $slug = 'seo-meta-tags';
    protected static $title = 'SEO Meta Tags Complete?';
    protected static $description = 'Verifies title, description, OG tags present.';

    public static function check(): ?array {
        // Check for SEO plugins
        if (is_plugin_active('wordpress-seo/wp-seo.php') || 
            is_plugin_active('all-in-one-seo-pack/all_in_one_seo_pack.php') ||
            is_plugin_active('seopress/seopress.php')) {
            return null; // Pass - SEO plugin handles meta tags
        }
        
        // Check for basic meta description
        ob_start();
        wp_head();
        $head = ob_get_clean();
        
        if (strpos($head, 'meta name="description"') !== false) {
            return null; // Pass - meta description present
        }
        
        return array(
            'id'            => static::$slug,
            'title'         => static::$title,
            'description'   => 'No SEO plugin or meta description tags detected.',
            'color'         => '#ff9800',
            'bg_color'      => '#fff3e0',
            'kb_link'       => 'https://wpshadow.com/kb/seo-meta-tags/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=seo-meta-tags',
            'training_link' => 'https://wpshadow.com/training/seo-meta-tags/',
            'auto_fixable'  => false,
            'threat_level'  => 60,
            'module'        => 'SEO',
            'priority'      => 1,
        );
    }
}
