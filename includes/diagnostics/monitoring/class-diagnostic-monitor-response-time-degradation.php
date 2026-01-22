<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Response_Time_Degradation extends Diagnostic_Base {
    public static function check(): ?array {
        return ['id' => 'monitor-response-degradation', 'title' => __('Response Time Degradation', 'wpshadow'), 'description' => __('Detects when page load time increases by 50%+ from baseline. Indicates infrastructure, plugin, or code performance issue.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/performance-monitoring/', 'training_link' => 'https://wpshadow.com/training/speed-optimization/', 'auto_fixable' => false, 'threat_level' => 7];
    }
}
