<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_Plugin_Conflict_Detection.php {
    public static function check() {
        return ['id' => 'monitor-plugin-conflicts', 'title' => __('Plugin Conflict Detection', 'wpshadow'), 'description' => __('Detects fatal errors, 500 errors caused by plugin incompatibility. Identifies conflicting plugins for disabling.', 'wpshadow'), 'severity' => 'high', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/plugin-compatibility/', 'training_link' => 'https://wpshadow.com/training/conflict-troubleshooting/', 'auto_fixable' => false, 'threat_level' => 8];
    }
}
