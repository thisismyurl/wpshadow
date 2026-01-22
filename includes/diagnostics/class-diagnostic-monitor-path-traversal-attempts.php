<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_Path_Traversal_Attempts {
    public static function check() {
        return ['id' => 'monitor-path-traversal', 'title' => __('Path Traversal Attack Detection', 'wpshadow'), 'description' => __('Detects directory traversal attempts (../, ../../, ..\\). Prevents file access outside intended directories.', 'wpshadow'), 'severity' => 'critical', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/file-access-security/', 'training_link' => 'https://wpshadow.com/training/access-control/', 'auto_fixable' => false, 'threat_level' => 10];
    }
}
