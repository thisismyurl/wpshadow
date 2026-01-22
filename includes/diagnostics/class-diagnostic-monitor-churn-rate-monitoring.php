<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_Churn-rate-monitoring {
  public static function check() {
    return ['id' => 'monitor-churn_rate_monitoring', 'title' => __('User Churn Rate Monitoring', 'wpshadow'), 'description' => __('Tracks unsubscribe/cancellation rates. Increasing = satisfaction issue.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
