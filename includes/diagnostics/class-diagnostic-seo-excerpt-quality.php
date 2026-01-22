<?php declare(strict_types=1);
/**
 * Excerpt Quality Diagnostic
 *
 * Philosophy: Hand-written excerpts improve CTR
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Excerpt_Quality {
    public static function check() {
        global $wpdb;
        $total = (int) $wpdb->get_var("SELECT COUNT(1) FROM {$wpdb->posts} WHERE post_status = 'publish' AND post_type = 'post'");
        $with_excerpt = (int) $wpdb->get_var("SELECT COUNT(1) FROM {$wpdb->posts} WHERE post_status = 'publish' AND post_type = 'post' AND post_excerpt != ''");
        $missing = $total - $with_excerpt;
        if ($missing > 10 && $missing > ($total * 0.3)) {
            return [
                'id' => 'seo-excerpt-quality',
                'title' => 'Hand-Written Excerpts Missing',
                'description' => sprintf('%d posts relying on auto-generated excerpts. Write custom excerpts for better meta descriptions.', $missing),
                'severity' => 'low',
                'category' => 'seo',
                'kb_link' => 'https://wpshadow.com/kb/excerpt-best-practices/',
                'training_link' => 'https://wpshadow.com/training/content-optimization/',
                'auto_fixable' => false,
                'threat_level' => 20,
            ];
        }
        return null;
    }
}
