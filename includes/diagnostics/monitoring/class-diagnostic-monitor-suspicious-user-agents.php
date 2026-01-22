<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Suspicious_User_Agents extends Diagnostic_Base {
    public static function check(): ?array {
        return ['id' => 'monitor-user-agents', 'title' => __('Suspicious User Agent Detection', 'wpshadow'), 'description' => __('Detects scanning tools, exploit frameworks, malicious bots. Distinguishes legitimate crawlers from reconnaissance tools.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/bot-management/', 'training_link' => 'https://wpshadow.com/training/traffic-filtering/', 'auto_fixable' => false, 'threat_level' => 6];
    }
}
