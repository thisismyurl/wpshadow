<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Customer extends Diagnostic_Base {
  public static function check(): ?array {
    return ['id' => 'monitor-customer_lifetime_value', 'title' => __('Customer Lifetime Value Trend', 'wpshadow'), 'description' => __('Tracks CLV changes. Declining CLV = product/service issue.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }

}