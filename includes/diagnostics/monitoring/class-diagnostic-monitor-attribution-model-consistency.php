<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

class Diagnostic_Monitor_Attribution_Model_Consistency extends Diagnostic_Base { public static function check(): ?array { return ['id' => 'monitor-attribution', 'title' => __('Attribution Model Consistency', 'wpshadow'), 'description' => __('Tracks whether attribution model is consistently applied. Changes indicate analytics config errors.', 'wpshadow'), 'severity' => 'low', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/attribution-models/', 'training_link' => 'https://wpshadow.com/training/analytics-modeling/', 'auto_fixable' => false, 'threat_level' => 2]; } }
