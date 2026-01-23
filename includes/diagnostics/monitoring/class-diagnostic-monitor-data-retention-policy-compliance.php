<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

class Diagnostic_Monitor_Data_Retention_Policy_Compliance extends Diagnostic_Base { public static function check(): ?array { return ['id' => 'monitor-retention-policy', 'title' => __('Data Retention Policy Compliance', 'wpshadow'), 'description' => __('Verifies old data deleted per retention policy. Excess data = legal liability, storage cost.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/data-retention/', 'training_link' => 'https://wpshadow.com/training/data-lifecycle/', 'auto_fixable' => false, 'threat_level' => 6]; } 
}