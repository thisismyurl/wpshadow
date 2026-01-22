<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Traffic_Anomaly_Detection extends Diagnostic_Base {
    public static function check(): ?array {
        return ['id' => 'monitor-traffic-anomaly', 'title' => __('Traffic Volume Anomaly Detection', 'wpshadow'), 'description' => __('Detects abnormal traffic spikes or drops. Sudden drop = server issues/outage. Spike = DDoS or viral content.', 'wpshadow'), 'severity' => 'high', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/traffic-health/', 'training_link' => 'https://wpshadow.com/training/analytics-monitoring/', 'auto_fixable' => false, 'threat_level' => 7];
    }
}
