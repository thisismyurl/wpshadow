<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Net extends Diagnostic_Base {
  public static function check(): ?array {
    return ['id' => 'monitor-net_promoter_score', 'title' => __('Net Promoter Score Tracking', 'wpshadow'), 'description' => __('Monitors NPS trends. Declining = customer satisfaction issue.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
