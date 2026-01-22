<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_Customer-acquisition-cost {
  public static function check() {
    return ['id' => 'monitor-customer_acquisition_cost', 'title' => __('Customer Acquisition Cost Efficiency', 'wpshadow'), 'description' => __('Monitors CAC per channel. Rising CAC = saturation, need new channels.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
