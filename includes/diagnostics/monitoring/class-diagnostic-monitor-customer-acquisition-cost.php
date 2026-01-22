<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Customer extends Diagnostic_Base {
  public static function check(): ?array {
    return ['id' => 'monitor-customer_acquisition_cost', 'title' => __('Customer Acquisition Cost Efficiency', 'wpshadow'), 'description' => __('Monitors CAC per channel. Rising CAC = saturation, need new channels.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
