<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Conversion_Rate_Anomaly extends Diagnostic_Base { public static function check(): ?array { return ['id' => 'monitor-conversion-anomaly', 'title' => __('Conversion Rate Anomaly Detection', 'wpshadow'), 'description' => __('Detects sudden conversion drops. Indicates checkout issues, payment processor failure, or user experience regression.', 'wpshadow'), 'severity' => 'critical', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/conversion-tracking/', 'training_link' => 'https://wpshadow.com/training/checkout-optimization/', 'auto_fixable' => false, 'threat_level' => 10]; } }
