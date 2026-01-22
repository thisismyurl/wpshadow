<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_DDoS_Attack_Patterns extends Diagnostic_Base {
    public static function check(): ?array {
        return ['id' => 'monitor-ddos', 'title' => __('DDoS/Volume Attack Detection', 'wpshadow'), 'description' => __('Detects HTTP floods, slowloris attacks, connection exhaustion. Identifies when volume overwhelms capacity.', 'wpshadow'), 'severity' => 'critical', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/ddos-mitigation/', 'training_link' => 'https://wpshadow.com/training/traffic-protection/', 'auto_fixable' => false, 'threat_level' => 10];
    }
}
