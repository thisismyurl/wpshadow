<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_SQL_Injection_Attempts extends Diagnostic_Base {
    public static function check(): ?array {
        // Check if security monitoring is active (Wordfence, Sucuri, etc)
        $monitoring_active = is_plugin_active('wordfence/wordfence.php') ||
                            is_plugin_active('sucuri-scanner/sucuri.php') ||
                            is_plugin_active('better-wp-security/better-wp-security.php');
        
        if ($monitoring_active) {
            return null; // Monitoring in place
        }
        
        return ['id' => 'monitor-sql-injection', 'title' => __('SQL Injection Monitoring Not Active', 'wpshadow'), 'description' => __('No security plugin monitoring SQL injection attempts. Install Wordfence or similar.', 'wpshadow'), 'severity' => 'low', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/sql-injection-prevention/', 'training_link' => 'https://wpshadow.com/training/security-hardening/', 'auto_fixable' => false, 'threat_level' => 10];
    }
}
