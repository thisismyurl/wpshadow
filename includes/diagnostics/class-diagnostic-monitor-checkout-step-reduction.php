<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_Checkout-step-reduction {
  public static function check() {
    return ['id' => 'monitor-checkout_step_reduction', 'title' => __('Checkout Step Optimization', 'wpshadow'), 'description' => __('Analyzes checkout funnel length. Too many steps = cart abandonment.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
