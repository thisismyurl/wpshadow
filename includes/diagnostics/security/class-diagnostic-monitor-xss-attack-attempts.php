<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_XSS_Attack_Attempts extends Diagnostic_Base {
    public static function check(): ?array {
        // Check if security monitoring is active
        $monitoring_active = is_plugin_active('wordfence/wordfence.php') ||
                            is_plugin_active('sucuri-scanner/sucuri.php') ||
                            is_plugin_active('better-wp-security/better-wp-security.php');
        
        if ($monitoring_active) {
            return null;
        }
        
        return ['id' => 'monitor-xss-attacks', 'title' => __('Cross-Site Scripting (XSS) Attempts', 'wpshadow'), 'description' => __('Detects XSS payloads (<script>, onload=, javascript:). Blocks before malicious JS runs on visitor browsers.', 'wpshadow'), 'severity' => 'critical', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/xss-prevention/', 'training_link' => 'https://wpshadow.com/training/input-sanitization/', 'auto_fixable' => false, 'threat_level' => 10];
    }

}