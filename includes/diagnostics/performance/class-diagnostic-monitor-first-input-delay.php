<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_First_Input_Delay extends Diagnostic_Base { public static function check(): ?array { return ['id' => 'monitor-fid', 'title' => __('First Input Delay Monitoring', 'wpshadow'), 'description' => __('FID > 100ms = poor interactivity, ranking penalty. Indicates JavaScript blocking main thread.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/interactivity/', 'training_link' => 'https://wpshadow.com/training/js-optimization/', 'auto_fixable' => false, 'threat_level' => 6]; } 
}