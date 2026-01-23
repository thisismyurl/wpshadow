<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

class Diagnostic_Monitor_Site_Health_Score extends Diagnostic_Base { public static function check(): ?array { return ['id' => 'monitor-site-health', 'title' => __('Overall Site Health Score', 'wpshadow'), 'description' => __('Composite score: uptime, speed, security, errors, updates. Guides prioritization of fixes.', 'wpshadow'), 'severity' => 'high', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/site-health/', 'training_link' => 'https://wpshadow.com/training/maintenance/', 'auto_fixable' => false, 'threat_level' => 8]; } 
}