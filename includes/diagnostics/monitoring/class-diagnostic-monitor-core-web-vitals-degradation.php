<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Core_Web_Vitals_Degradation extends Diagnostic_Base { public static function check(): ?array { return ['id' => 'monitor-cwv-degradation', 'title' => __('Core Web Vitals Degradation', 'wpshadow'), 'description' => __('Tracks LCP, FID, CLS scores. Degradation = ranking impact, traffic loss. Immediate optimization needed.', 'wpshadow'), 'severity' => 'high', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/web-vitals/', 'training_link' => 'https://wpshadow.com/training/page-speed/', 'auto_fixable' => false, 'threat_level' => 8]; } 
}