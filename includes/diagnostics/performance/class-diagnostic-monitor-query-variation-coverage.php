<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

class Diagnostic_Monitor_Query_Variation_Coverage extends Diagnostic_Base { public static function check(): ?array { return ['id' => 'monitor-query-variants', 'title' => __('Query Variation Coverage Tracking', 'wpshadow'), 'description' => __('Monitors if you rank for query variations. Low coverage = missed long-tail traffic.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/query-variants/', 'training_link' => 'https://wpshadow.com/training/keyword-variations/', 'auto_fixable' => false, 'threat_level' => 6]; } 
}