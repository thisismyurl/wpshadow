<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

class Diagnostic_Monitor_Lazy_Loading_Effectiveness extends Diagnostic_Base { public static function check(): ?array { return ['id' => 'monitor-lazy-load', 'title' => __('Lazy Loading Effectiveness', 'wpshadow'), 'description' => __('Verifies lazy loading working. Broken lazy loading = all images load on page load, slow initial paint.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/lazy-loading/', 'training_link' => 'https://wpshadow.com/training/loading-strategies/', 'auto_fixable' => false, 'threat_level' => 5]; } 
}