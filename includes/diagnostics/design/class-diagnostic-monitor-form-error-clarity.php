<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Form extends Diagnostic_Base {
  public static function check(): ?array {
    return ['id' => 'monitor-form_error_clarity', 'title' => __('Form Error Message Clarity', 'wpshadow'), 'description' => __('Verifies error messages helpful. Vague errors = user frustration, abandonment.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }

}