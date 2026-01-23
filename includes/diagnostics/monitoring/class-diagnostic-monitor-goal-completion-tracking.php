<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

class Diagnostic_Monitor_Goal_Completion_Tracking extends Diagnostic_Base { public static function check(): ?array { return ['id' => 'monitor-goal-completion', 'title' => __('Goal Completion Rate Tracking', 'wpshadow'), 'description' => __('Tracks % of visitors completing defined goals. Drop indicates funnel breakage or user friction.', 'wpshadow'), 'severity' => 'high', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/goal-tracking/', 'training_link' => 'https://wpshadow.com/training/funnel-optimization/', 'auto_fixable' => false, 'threat_level' => 8]; } 
}