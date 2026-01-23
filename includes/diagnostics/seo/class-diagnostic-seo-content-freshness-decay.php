<?php
declare(strict_types=1);
/**
 * Content Freshness Decay Diagnostic
 *
 * Philosophy: Old content needs updates
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Content_Freshness_Decay extends Diagnostic_Base {
    public static function check(): ?array {
        global $wpdb;
        $old_posts = (int) $wpdb->get_var("SELECT COUNT(1) FROM {$wpdb->posts} WHERE post_status = 'publish' AND post_type = 'post' AND post_modified < DATE_SUB(NOW(), INTERVAL 2 YEAR)");
        if ($old_posts > 20) {
            return [
                'id' => 'seo-content-freshness-decay',
                'title' => 'Content Freshness Decay',
                'description' => sprintf('%d posts not updated in 2+ years. Review and refresh outdated content.', $old_posts),
                'severity' => 'medium',
                'category' => 'seo',
                'kb_link' => 'https://wpshadow.com/kb/content-freshness/',
                'training_link' => 'https://wpshadow.com/training/content-updates/',
                'auto_fixable' => false,
                'threat_level' => 40,
            ];
        }
        return null;
    }

}