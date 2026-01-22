<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Path_Traversal_Attempts extends Diagnostic_Base {
    public static function check(): ?array {
        // Check if security monitoring is active
        $monitoring_active = is_plugin_active('wordfence/wordfence.php') ||
                            is_plugin_active('sucuri-scanner/sucuri.php') ||
                            is_plugin_active('better-wp-security/better-wp-security.php');
        
        if ($monitoring_active) {
            return null;
        }
        
        return ['id' => 'monitor-path-traversal', 'title' => __('Path Traversal Attack Detection', 'wpshadow'), 'description' => __('Detects directory traversal attempts (../, ../../, ..\\). Prevents file access outside intended directories.', 'wpshadow'), 'severity' => 'critical', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/file-access-security/', 'training_link' => 'https://wpshadow.com/training/access-control/', 'auto_fixable' => false, 'threat_level' => 10];
    }
}
