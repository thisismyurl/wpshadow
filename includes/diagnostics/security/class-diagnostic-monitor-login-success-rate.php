<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

class Diagnostic_Monitor_Login_Success_Rate extends Diagnostic_Base { public static function check(): ?array {
        // Check if monitoring plugins are active
        $has_monitoring = is_plugin_active('wordfence/wordfence.php') || 
                         is_plugin_active('sucuri-scanner/sucuri.php');
        if ($has_monitoring) {
            return null; // Monitoring in place
        }
        
return ['id' => 'monitor-login-success', 'title' => __('Login Success Rate', 'wpshadow'), 'description' => __('Tracks successful logins. Drop indicates auth system failure, 2FA issues, or password reset malfunction.', 'wpshadow'), 'severity' => 'high', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/auth-monitoring/', 'training_link' => 'https://wpshadow.com/training/authentication/', 'auto_fixable' => false, 'threat_level' => 8]; } }
