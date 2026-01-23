<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Rate_Limit_Violations extends Diagnostic_Base {
    public static function check(): ?array {
        return ['id' => 'monitor-rate-limits', 'title' => __('Rate Limit Violation Detection', 'wpshadow'), 'description' => __('Detects when IP/user exceeds request limits (API abuse, credential stuffing, data harvesting). Blocks before damage.', 'wpshadow'), 'severity' => 'high', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/api-protection/', 'training_link' => 'https://wpshadow.com/training/rate-limiting/', 'auto_fixable' => false, 'threat_level' => 8];
    }

}