<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Site_Uptime_Status extends Diagnostic_Base {
    public static function check(): ?array {
        return ['id' => 'monitor-uptime', 'title' => __('Site Uptime Status', 'wpshadow'), 'description' => __('Continuously monitors if site is reachable via HTTP/HTTPS from multiple geographic locations. Detects outages within seconds.', 'wpshadow'), 'severity' => 'high', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/uptime-monitoring/', 'training_link' => 'https://wpshadow.com/training/site-availability/', 'auto_fixable' => false, 'threat_level' => 10];
    }
}
