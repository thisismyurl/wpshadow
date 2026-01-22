<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_Payment-option-diversity {
  public static function check() {
    return ['id' => 'monitor-payment_option_diversity', 'title' => __('Payment Option Diversity', 'wpshadow'), 'description' => __('Tracks available payment methods. Limited options = abandoned carts.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
