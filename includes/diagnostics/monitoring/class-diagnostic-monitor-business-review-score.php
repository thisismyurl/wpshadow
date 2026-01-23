<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

class Diagnostic_Monitor_Business_Review_Score extends Diagnostic_Base { public static function check(): ?array { return ['id' => 'monitor-review-score', 'title' => __('Business Review Score Monitoring', 'wpshadow'), 'description' => __('Tracks average rating across Google, Yelp, Facebook. Score impacts local pack ranking and trust signals.', 'wpshadow'), 'severity' => 'high', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/reputation-management/', 'training_link' => 'https://wpshadow.com/training/review-strategy/', 'auto_fixable' => false, 'threat_level' => 8]; } 
}