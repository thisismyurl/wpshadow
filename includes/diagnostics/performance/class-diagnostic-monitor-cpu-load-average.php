<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_CPU_Load_Average extends Diagnostic_Base {
    public static function check(): ?array {
        return ['id' => 'monitor-cpu-load', 'title' => __('CPU Load Average Monitoring', 'wpshadow'), 'description' => __('Tracks system CPU load. High load = slow requests, timeouts, unhappy users. Indicates need for optimization or resource upgrade.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/cpu-optimization/', 'training_link' => 'https://wpshadow.com/training/server-tuning/', 'auto_fixable' => false, 'threat_level' => 6];
    }
}
