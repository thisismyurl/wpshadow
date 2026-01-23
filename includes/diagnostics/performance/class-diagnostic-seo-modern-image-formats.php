<?php
declare(strict_types=1);
/**
 * Modern Image Formats Diagnostic
 *
 * Philosophy: Use WebP/AVIF for faster loads
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Modern_Image_Formats extends Diagnostic_Base {
    /**
     * Check if any WebP attachments exist as a proxy for modern formats usage.
     *
     * @return array|null
     */
    public static function check(): ?array {
        global $wpdb;
        $count = (int) $wpdb->get_var("SELECT COUNT(1) FROM {$wpdb->posts} p JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id WHERE p.post_type='attachment' AND pm.meta_key='_wp_attachment_metadata' AND pm.meta_value LIKE '%webp%' LIMIT 1");
        if ($count > 0) {
            return null;
        }
        return [
            'id' => 'seo-modern-image-formats',
            'title' => 'Use Modern Image Formats',
            'description' => 'Consider using WebP or AVIF for large images to improve performance and Web Vitals.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/webp-avif-images/',
            'training_link' => 'https://wpshadow.com/training/image-optimization/',
            'auto_fixable' => false,
            'threat_level' => 15,
        ];
    }

}