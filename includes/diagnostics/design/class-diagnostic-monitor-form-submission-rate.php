<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Form_Submission_Rate extends Diagnostic_Base { public static function check(): ?array { return ['id' => 'monitor-form-submissions', 'title' => __('Form Submission Rate Monitoring', 'wpshadow'), 'description' => __('Tracks contact form, newsletter signup submissions. Drop indicates broken form, CSRF issues, or spam filters blocking.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/form-monitoring/', 'training_link' => 'https://wpshadow.com/training/form-optimization/', 'auto_fixable' => false, 'threat_level' => 6]; } }
