<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

class Diagnostic_Monitor_Local_Pack_Position extends Diagnostic_Base { public static function check(): ?array { return ['id' => 'monitor-local-pack', 'title' => __('Local Pack Position Monitoring', 'wpshadow'), 'description' => __('Tracks position in local 3-pack for location queries. Position 1-3 = 90% traffic. Outside = invisible.', 'wpshadow'), 'severity' => 'high', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/local-seo/', 'training_link' => 'https://wpshadow.com/training/local-optimization/', 'auto_fixable' => false, 'threat_level' => 8]; } 
}