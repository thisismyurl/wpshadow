<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Churn extends Diagnostic_Base {
  public static function check(): ?array {
    return ['id' => 'monitor-churn_rate_monitoring', 'title' => __('User Churn Rate Monitoring', 'wpshadow'), 'description' => __('Tracks unsubscribe/cancellation rates. Increasing = satisfaction issue.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
