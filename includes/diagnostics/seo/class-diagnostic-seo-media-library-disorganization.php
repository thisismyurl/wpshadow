<?php
declare(strict_types=1);
/**
 * Media Library Disorganization Diagnostic
 *
 * Philosophy: Organized media improves management
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Media_Library_Disorganization extends Diagnostic_Base {
    public static function check(): ?array {
        global $wpdb;
        $unattached = (int) $wpdb->get_var("SELECT COUNT(1) FROM {$wpdb->posts} WHERE post_type = 'attachment' AND post_parent = 0");
        if ($unattached > 500) {
            return [
                'id' => 'seo-media-library-disorganization',
                'title' => 'Unattached Media Files',
                'description' => sprintf('%d unattached media files. Consider organizing media library for better management.', $unattached),
                'severity' => 'low',
                'category' => 'seo',
                'kb_link' => 'https://wpshadow.com/kb/media-organization/',
                'training_link' => 'https://wpshadow.com/training/media-management/',
                'auto_fixable' => false,
                'threat_level' => 10,
            ];
        }
        return null;
    }
}
