<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Mixed_Content_Warnings extends Diagnostic_Base {
    public static function check(): ?array {
        // Check if monitoring plugins are active
        $has_monitoring = is_plugin_active('wordfence/wordfence.php') || 
                         is_plugin_active('sucuri-scanner/sucuri.php');
        if ($has_monitoring) {
            return null; // Monitoring in place
        }
        
return ['id' => 'monitor-mixed-content', 'title' => __('Mixed Content Detection', 'wpshadow'), 'description' => __('Detects HTTP content served on HTTPS pages. Blocks images, scripts, causing broken site and browser warnings.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/https-migration/', 'training_link' => 'https://wpshadow.com/training/https-setup/', 'auto_fixable' => false, 'threat_level' => 6];
    }
}
