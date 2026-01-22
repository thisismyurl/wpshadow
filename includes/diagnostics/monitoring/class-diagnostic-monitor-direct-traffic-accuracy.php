<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

class Diagnostic_Monitor_Direct_Traffic_Accuracy extends Diagnostic_Base { public static function check(): ?array { return ['id' => 'monitor-direct-traffic', 'title' => __('Direct Traffic Accuracy Tracking', 'wpshadow'), 'description' => __('Monitors direct visits. Spike may indicate bookmarking trend or analytics attribution errors.', 'wpshadow'), 'severity' => 'low', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/traffic-attribution/', 'training_link' => 'https://wpshadow.com/training/analytics-setup/', 'auto_fixable' => false, 'threat_level' => 3]; } }
