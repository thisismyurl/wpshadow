<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Audit Trail Logging Active?
 * 
 * Target Persona: Enterprise IT/Compliance Team
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
 */
class Diagnostic_Audit_Logging extends Diagnostic_Base {
    protected static $slug = 'audit-logging';
    protected static $title = 'Audit Trail Logging Active?';
    protected static $description = 'Verifies user action logging is enabled.';


    public static function check(): ?array {
        // Check for common audit logging plugins
        $audit_plugins = array(
            'wp-security-audit-log/wp-security-audit-log.php',
            'activity-log/aryo-activity-log.php',
            'simple-history/index.php',
            'stream/stream.php',
        );
        
        $has_audit_logging = false;
        foreach ($audit_plugins as $plugin) {
            if (is_plugin_active($plugin)) {
                $has_audit_logging = true;
                break;
            }
        }
        
        if (!$has_audit_logging) {
            return array(
                'id'            => static::$slug,
                'title'         => static::$title,
                'description'   => 'No audit logging plugin detected. User actions are not being tracked.',
                'severity'      => 'medium',
                'category'      => 'security',
                'kb_link'       => 'https://wpshadow.com/kb/audit-logging/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=audit-logging',
                'training_link' => 'https://wpshadow.com/training/audit-logging/',
                'auto_fixable'  => false,
                'threat_level'  => 60,
                'module'        => 'Security',
                'priority'      => 1,
            );
        }
        
        return null;
    }
}
