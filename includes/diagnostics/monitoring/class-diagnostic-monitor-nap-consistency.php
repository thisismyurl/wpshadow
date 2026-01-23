<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

class Diagnostic_Monitor_NAP_Consistency extends Diagnostic_Base { public static function check(): ?array { return ['id' => 'monitor-nap-consistency', 'title' => __('NAP Consistency Across Citations', 'wpshadow'), 'description' => __('Monitors Name/Address/Phone consistency across directories. Inconsistency = local ranking penalty and confusion.', 'wpshadow'), 'severity' => 'high', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/nap-consistency/', 'training_link' => 'https://wpshadow.com/training/citation-management/', 'auto_fixable' => false, 'threat_level' => 8]; } 
}