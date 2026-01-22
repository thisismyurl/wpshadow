<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

class Diagnostic_Monitor_Font_Loading_Performance extends Diagnostic_Base { public static function check(): ?array { return ['id' => 'monitor-font-performance', 'title' => __('Font Loading Performance', 'wpshadow'), 'description' => __('Monitors web font loading time and FOUT/FOIT. Slow fonts = CLS issues, text invisible briefly.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/font-optimization/', 'training_link' => 'https://wpshadow.com/training/font-strategy/', 'auto_fixable' => false, 'threat_level' => 5]; } }
