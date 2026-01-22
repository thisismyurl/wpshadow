<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Brute_Force_Attempts extends Diagnostic_Base {
    public static function check(): ?array {
        // Check if monitoring plugins are active
        $has_monitoring = is_plugin_active('wordfence/wordfence.php') || 
                         is_plugin_active('sucuri-scanner/sucuri.php');
        if ($has_monitoring) {
            return null; // Monitoring in place
        }
        
return ['id' => 'monitor-brute-force', 'title' => __('Brute Force Attack Detection', 'wpshadow'), 'description' => __('Detects multiple failed login attempts from same IP. Real-time alert enables quick block before accounts compromised.', 'wpshadow'), 'severity' => 'high', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/login-security/', 'training_link' => 'https://wpshadow.com/training/brute-force-prevention/', 'auto_fixable' => false, 'threat_level' => 9];
    }
}
