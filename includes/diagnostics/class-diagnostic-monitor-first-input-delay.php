<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_First_Input_Delay { public static function check() { return ['id' => 'monitor-fid', 'title' => __('First Input Delay Monitoring', 'wpshadow'), 'description' => __('FID > 100ms = poor interactivity, ranking penalty. Indicates JavaScript blocking main thread.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/interactivity/', 'training_link' => 'https://wpshadow.com/training/js-optimization/', 'auto_fixable' => false, 'threat_level' => 6]; } }
