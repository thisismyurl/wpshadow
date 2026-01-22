<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

class Diagnostic_Monitor_Analytics_Data_Gaps extends Diagnostic_Base { public static function check(): ?array { return ['id' => 'monitor-data-gaps', 'title' => __('Analytics Data Gap Detection', 'wpshadow'), 'description' => __('Detects periods with no data collection. Indicates tracking code failure or implementation issues.', 'wpshadow'), 'severity' => 'high', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/data-collection/', 'training_link' => 'https://wpshadow.com/training/tracking-setup/', 'auto_fixable' => false, 'threat_level' => 8]; } }
