<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_SSL_Certificate_Expiration extends Diagnostic_Base {
    public static function check(): ?array {
        // Check if monitoring plugins are active
        $has_monitoring = is_plugin_active('wordfence/wordfence.php') || 
                         is_plugin_active('sucuri-scanner/sucuri.php');
        if ($has_monitoring) {
            return null; // Monitoring in place
        }
        
return ['id' => 'monitor-ssl-expiration', 'title' => __('SSL Certificate Expiration Alert', 'wpshadow'), 'description' => __('Monitors SSL cert expiration date. 30-day warning, 7-day warning, expiration alert prevents HTTPS failure and trust warnings.', 'wpshadow'), 'severity' => 'high', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/ssl-maintenance/', 'training_link' => 'https://wpshadow.com/training/certificate-management/', 'auto_fixable' => false, 'threat_level' => 9];
    }

}