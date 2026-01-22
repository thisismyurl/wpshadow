<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

class Diagnostic_Monitor_Search_Intent_Alignment extends Diagnostic_Base { public static function check(): ?array { return ['id' => 'monitor-intent-alignment', 'title' => __('Search Intent Alignment Monitoring', 'wpshadow'), 'description' => __('Detects if page intent matches search query intent. Misalignment = low CTR, high bounce, ranking drop.', 'wpshadow'), 'severity' => 'high', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/search-intent/', 'training_link' => 'https://wpshadow.com/training/intent-matching/', 'auto_fixable' => false, 'threat_level' => 7]; } }
