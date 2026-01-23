<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_TLS_Handshake_Time extends Diagnostic_Base { public static function check(): ?array {
        // Check if monitoring plugins are active
        $has_monitoring = is_plugin_active('wordfence/wordfence.php') || 
                         is_plugin_active('sucuri-scanner/sucuri.php');
        if ($has_monitoring) {
            return null; // Monitoring in place
        }
        
return ['id' => 'monitor-tls-time', 'title' => __('TLS Handshake Time Monitoring', 'wpshadow'), 'description' => __('Tracks HTTPS negotiation time. Slow TLS = TTFB impact. Indicates weak cipher or certificate issue.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/tls-optimization/', 'training_link' => 'https://wpshadow.com/training/https-performance/', 'auto_fixable' => false, 'threat_level' => 5]; } 
}