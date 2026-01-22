<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Testimonial extends Diagnostic_Base {
  public static function check(): ?array {
    return ['id' => 'monitor-testimonial_currency', 'title' => __('Testimonial Freshness', 'wpshadow'), 'description' => __('Checks testimonial dates. Old testimonials = credibility loss.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
