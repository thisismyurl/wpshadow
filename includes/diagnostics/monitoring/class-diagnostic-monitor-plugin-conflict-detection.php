<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Plugin_Conflict_Detection extends Diagnostic_Base .php {
    public static function check(): ?array {
        return ['id' => 'monitor-plugin-conflicts', 'title' => __('Plugin Conflict Detection', 'wpshadow'), 'description' => __('Detects fatal errors, 500 errors caused by plugin incompatibility. Identifies conflicting plugins for disabling.', 'wpshadow'), 'severity' => 'high', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/plugin-compatibility/', 'training_link' => 'https://wpshadow.com/training/conflict-troubleshooting/', 'auto_fixable' => false, 'threat_level' => 8];
    }
}
