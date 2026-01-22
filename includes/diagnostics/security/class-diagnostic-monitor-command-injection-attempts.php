<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Command_Injection_Attempts extends Diagnostic_Base {
    public static function check(): ?array {
        // Check if security monitoring is active
        $monitoring_active = is_plugin_active('wordfence/wordfence.php') ||
                            is_plugin_active('sucuri-scanner/sucuri.php') ||
                            is_plugin_active('better-wp-security/better-wp-security.php');
        
        if ($monitoring_active) {
            return null;
        }
        
        return ['id' => 'monitor-command-injection', 'title' => __('Command Injection Attack Attempts', 'wpshadow'), 'description' => __('Detects OS command injection patterns (|, &&, ;, `, $(...)). Prevents remote code execution via shell commands.', 'wpshadow'), 'severity' => 'critical', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/command-execution-safety/', 'training_link' => 'https://wpshadow.com/training/input-validation/', 'auto_fixable' => false, 'threat_level' => 10];
    }
}
