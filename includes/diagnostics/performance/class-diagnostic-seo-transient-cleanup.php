<?php
declare(strict_types=1);
/**
 * Transient Cleanup Diagnostic
 *
 * Philosophy: Expired transients bloat database
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Transient_Cleanup extends Diagnostic_Base {
    public static function check(): ?array {
        global $wpdb;
        $expired = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(1) FROM {$wpdb->options} WHERE option_name LIKE %s AND option_value < %d", '_transient_timeout_%', time()));
        if ($expired > 100) {
            return [
                'id' => 'seo-transient-cleanup',
                'title' => 'Expired Transients Need Cleanup',
                'description' => sprintf('%d expired transients detected. Clean up to reduce database size and improve query performance.', $expired),
                'severity' => 'low',
                'category' => 'seo',
                'kb_link' => 'https://wpshadow.com/kb/transient-cleanup/',
                'training_link' => 'https://wpshadow.com/training/database-optimization/',
                'auto_fixable' => false,
                'threat_level' => 20,
            ];
        }
        return null;
    }
}
