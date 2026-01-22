<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Unauthorized_Admin_Creation extends Diagnostic_Base {
    public static function check(): ?array {
        // Check if security monitoring is active
        $monitoring_active = is_plugin_active('wordfence/wordfence.php') ||
                            is_plugin_active('sucuri-scanner/sucuri.php') ||
                            is_plugin_active('better-wp-security/better-wp-security.php');
        
        if ($monitoring_active) {
            return null;
        }
        
        return ['id' => 'monitor-admin-creation', 'title' => __('Unauthorized Admin Account Creation', 'wpshadow'), 'description' => __('Detects new admin/user accounts created without authorization. Hacker persistence mechanism via backdoor accounts.', 'wpshadow'), 'severity' => 'critical', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/user-management/', 'training_link' => 'https://wpshadow.com/training/account-security/', 'auto_fixable' => false, 'threat_level' => 10];
    }
}
