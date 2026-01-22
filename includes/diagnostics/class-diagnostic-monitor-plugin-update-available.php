<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_Plugin_Update_Available.php {
    public static function check() {
        return ['id' => 'monitor-plugin-updates', 'title' => __('Plugin Update Availability', 'wpshadow'), 'description' => __('Tracks when plugin updates are available. Timely updates = security patches, performance fixes, compatibility.', 'wpshadow'), 'severity' => 'high', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/plugin-management/', 'training_link' => 'https://wpshadow.com/training/update-strategy/', 'auto_fixable' => false, 'threat_level' => 7];
    }
}
