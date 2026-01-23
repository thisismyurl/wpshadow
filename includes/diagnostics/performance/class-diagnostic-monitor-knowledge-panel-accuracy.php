<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

class Diagnostic_Monitor_Knowledge_Panel_Accuracy extends Diagnostic_Base { public static function check(): ?array { return ['id' => 'monitor-knowledge-panel', 'title' => __('Knowledge Panel Accuracy Monitoring', 'wpshadow'), 'description' => __('Monitors if Knowledge Panel shows correct info. Inaccurate = wrong phone, address, hours damages trust.', 'wpshadow'), 'severity' => 'high', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/knowledge-panel/', 'training_link' => 'https://wpshadow.com/training/knowledge-graph/', 'auto_fixable' => false, 'threat_level' => 7]; } 
}