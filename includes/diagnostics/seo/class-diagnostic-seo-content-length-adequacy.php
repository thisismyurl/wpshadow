<?php
declare(strict_types=1);
/**
 * Content Length Adequacy Diagnostic
 *
 * Philosophy: Depth signals comprehensive coverage
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Content_Length_Adequacy extends Diagnostic_Base {
    public static function check(): ?array {
        global $wpdb;
        $thin_content = (int) $wpdb->get_var("SELECT COUNT(1) FROM {$wpdb->posts} WHERE post_status = 'publish' AND post_type = 'post' AND CHAR_LENGTH(post_content) < 300");
        if ($thin_content > 10) {
            return [
                'id' => 'seo-content-length-adequacy',
                'title' => 'Content Depth and Length',
                'description' => sprintf('%d posts under 300 characters. Expand thin content for better coverage and value.', $thin_content),
                'severity' => 'medium',
                'category' => 'seo',
                'kb_link' => 'https://wpshadow.com/kb/content-depth/',
                'training_link' => 'https://wpshadow.com/training/comprehensive-content/',
                'auto_fixable' => false,
                'threat_level' => 45,
            ];
        }
        return null;
    }

}