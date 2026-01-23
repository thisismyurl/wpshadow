<?php
declare(strict_types=1);
/**
 * Custom Post Type Visibility Diagnostic
 *
 * Philosophy: CPTs should be in sitemaps
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Custom_Post_Type_Visibility extends Diagnostic_Base {
    public static function check(): ?array {
        $post_types = get_post_types(['public' => true, '_builtin' => false], 'names');
        if (count($post_types) > 0) {
            return [
                'id' => 'seo-custom-post-type-visibility',
                'title' => 'Custom Post Type Sitemap Visibility',
                'description' => 'Verify custom post types are included in XML sitemaps and properly indexed.',
                'severity' => 'low',
                'category' => 'seo',
                'kb_link' => 'https://wpshadow.com/kb/custom-post-types-seo/',
                'training_link' => 'https://wpshadow.com/training/cpt-optimization/',
                'auto_fixable' => false,
                'threat_level' => 25,
            ];
        }
        return null;
    }

}