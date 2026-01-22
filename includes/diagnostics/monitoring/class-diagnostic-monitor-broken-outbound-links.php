<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Broken extends Diagnostic_Base {
  public static function check(): ?array {
    return ['id' => 'monitor-broken_outbound_links', 'title' => __('Broken Outbound Links Detection', 'wpshadow'), 'description' => __('Finds external links returning 404. Indicates poor maintenance or link rot.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
