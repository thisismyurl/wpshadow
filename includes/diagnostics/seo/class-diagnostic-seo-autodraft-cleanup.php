<?php
declare(strict_types=1);
/**
 * Auto-Draft Cleanup Diagnostic
 *
 * Philosophy: Orphaned auto-drafts clutter database
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_AutoDraft_Cleanup extends Diagnostic_Base {
    public static function check(): ?array {
        global $wpdb;
        $autodrafts = (int) $wpdb->get_var("SELECT COUNT(1) FROM {$wpdb->posts} WHERE post_status = 'auto-draft' AND post_modified < DATE_SUB(NOW(), INTERVAL 7 DAY)");
        if ($autodrafts > 50) {
            return [
                'id' => 'seo-autodraft-cleanup',
                'title' => 'Orphaned Auto-Drafts',
                'description' => sprintf('%d old auto-drafts detected. Clean up to reduce database bloat.', $autodrafts),
                'severity' => 'low',
                'category' => 'seo',
                'kb_link' => 'https://wpshadow.com/kb/autodraft-cleanup/',
                'training_link' => 'https://wpshadow.com/training/database-optimization/',
                'auto_fixable' => false,
                'threat_level' => 15,
            ];
        }
        return null;
    }
}
