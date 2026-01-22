<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Bounce_Rate_Change extends Diagnostic_Base {
    public static function check(): ?array {
        return ['id' => 'monitor-bounce-rate', 'title' => __('Bounce Rate Anomaly Detection', 'wpshadow'), 'description' => __('Detects sudden bounce rate increase. Indicates poor content fit, technical issue, or bad traffic source.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/user-engagement/', 'training_link' => 'https://wpshadow.com/training/landing-page-optimization/', 'auto_fixable' => false, 'threat_level' => 6];
    }
}
