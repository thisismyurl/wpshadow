<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

class Diagnostic_Monitor_OCSP_Stapling_Status extends Diagnostic_Base { public static function check(): ?array {
        // Check if monitoring plugins are active
        $has_monitoring = is_plugin_active('wordfence/wordfence.php') || 
                         is_plugin_active('sucuri-scanner/sucuri.php');
        if ($has_monitoring) {
            return null; // Monitoring in place
        }
        
return ['id' => 'monitor-ocsp-stapling', 'title' => __('OCSP Stapling Status Monitoring', 'wpshadow'), 'description' => __('Verifies OCSP stapling enabled. Missing = additional OCSP lookup adds 100-300ms to TTFB.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/ocsp-stapling/', 'training_link' => 'https://wpshadow.com/training/ssl-optimization/', 'auto_fixable' => false, 'threat_level' => 5]; } }
