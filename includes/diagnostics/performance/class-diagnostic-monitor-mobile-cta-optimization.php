<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Mobile extends Diagnostic_Base {
  public static function check(): ?array {
    return ['id' => 'monitor-mobile_cta_optimization', 'title' => __('Mobile CTA Optimization', 'wpshadow'), 'description' => __('Verifies CTAs mobile-friendly. Mobile CTAs hidden = traffic loss on mobile.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }

}