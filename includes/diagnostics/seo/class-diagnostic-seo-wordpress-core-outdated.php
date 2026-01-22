<?php
declare(strict_types=1);
/**
 * WordPress Core Version Outdated Diagnostic
 *
 * Philosophy: Current version ensures performance/security
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_WordPress_Core_Outdated extends Diagnostic_Base {
    public static function check(): ?array {
        global $wp_version;
        $updates = get_core_updates();
        if (!empty($updates) && isset($updates[0]->response) && $updates[0]->response === 'upgrade') {
            return [
                'id' => 'seo-wordpress-core-outdated',
                'title' => 'WordPress Core Version Outdated',
                'description' => sprintf('WordPress %s is installed. Newer version %s available. Updates improve performance and security.', $wp_version, $updates[0]->version),
                'severity' => 'high',
                'category' => 'seo',
                'kb_link' => 'https://wpshadow.com/kb/wordpress-updates/',
                'training_link' => 'https://wpshadow.com/training/maintenance/',
                'auto_fixable' => false,
                'threat_level' => 70,
            ];
        }
        return null;
    }
}
