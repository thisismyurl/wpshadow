<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_Theme_Compatibility_Status.php {
    public static function check() {
        return ['id' => 'monitor-theme-compat', 'title' => __('Theme Compatibility Status', 'wpshadow'), 'description' => __('Tracks if current theme is compatible with active plugins and WordPress version. Prevents display/function breakage.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/theme-compatibility/', 'training_link' => 'https://wpshadow.com/training/theme-updates/', 'auto_fixable' => false, 'threat_level' => 6];
    }
}
