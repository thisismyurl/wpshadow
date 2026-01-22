<?php declare(strict_types=1);
/**
 * Revision Bloat Diagnostic
 *
 * Philosophy: Excessive revisions slow database
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Revision_Bloat {
    public static function check() {
        global $wpdb;
        $revisions = (int) $wpdb->get_var("SELECT COUNT(1) FROM {$wpdb->posts} WHERE post_type = 'revision'");
        if ($revisions > 1000) {
            return [
                'id' => 'seo-revision-bloat',
                'title' => 'Excessive Post Revisions',
                'description' => sprintf('%d revisions detected. Consider limiting revisions or cleaning old ones to improve database performance.', $revisions),
                'severity' => 'low',
                'category' => 'seo',
                'kb_link' => 'https://wpshadow.com/kb/revision-cleanup/',
                'training_link' => 'https://wpshadow.com/training/database-optimization/',
                'auto_fixable' => false,
                'threat_level' => 20,
            ];
        }
        return null;
    }
}
