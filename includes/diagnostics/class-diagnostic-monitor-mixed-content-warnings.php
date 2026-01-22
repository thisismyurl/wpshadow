<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_Mixed_Content_Warnings {
    public static function check() {
        return ['id' => 'monitor-mixed-content', 'title' => __('Mixed Content Detection', 'wpshadow'), 'description' => __('Detects HTTP content served on HTTPS pages. Blocks images, scripts, causing broken site and browser warnings.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/https-migration/', 'training_link' => 'https://wpshadow.com/training/https-setup/', 'auto_fixable' => false, 'threat_level' => 6];
    }
}
