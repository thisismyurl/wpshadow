<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Global_Availability_Gap extends Diagnostic_Base {
    public static function check(): ?array {
        return ['id' => 'monitor-global-gap', 'title' => __('Global Availability Gap', 'wpshadow'), 'description' => __('Tests from multiple regions (US, EU, Asia). Detects if site accessible from one region but not others. Indicates CDN or regional firewall issue.', 'wpshadow'), 'severity' => 'high', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/global-access/', 'training_link' => 'https://wpshadow.com/training/cdn-setup/', 'auto_fixable' => false, 'threat_level' => 8];
    }
}
