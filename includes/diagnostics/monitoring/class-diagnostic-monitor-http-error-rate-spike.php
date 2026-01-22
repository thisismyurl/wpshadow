<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_HTTP_Error_Rate_Spike extends Diagnostic_Base {
    public static function check(): ?array {
        return ['id' => 'monitor-error-spike', 'title' => __('HTTP Error Rate Spike', 'wpshadow'), 'description' => __('Monitors 5xx error rates. Spike indicates server misconfiguration, plugin conflict, or insufficient resources. Alerts before users notice.', 'wpshadow'), 'severity' => 'high', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/error-monitoring/', 'training_link' => 'https://wpshadow.com/training/troubleshooting/', 'auto_fixable' => false, 'threat_level' => 8];
    }
}
