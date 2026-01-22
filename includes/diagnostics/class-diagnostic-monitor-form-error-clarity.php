<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_Form-error-clarity {
  public static function check() {
    return ['id' => 'monitor-form_error_clarity', 'title' => __('Form Error Message Clarity', 'wpshadow'), 'description' => __('Verifies error messages helpful. Vague errors = user frustration, abandonment.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
