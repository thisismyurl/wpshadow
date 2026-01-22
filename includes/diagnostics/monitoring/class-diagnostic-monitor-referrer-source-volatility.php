<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

class Diagnostic_Monitor_Referrer_Source_Volatility extends Diagnostic_Base { public static function check(): ?array { return ['id' => 'monitor-referrer-volatility', 'title' => __('Referrer Source Volatility', 'wpshadow'), 'description' => __('Detects sudden changes in traffic sources. Loss of major source indicates partnership/integration failure.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/traffic-sources/', 'training_link' => 'https://wpshadow.com/training/multi-channel/', 'auto_fixable' => false, 'threat_level' => 6]; } }
