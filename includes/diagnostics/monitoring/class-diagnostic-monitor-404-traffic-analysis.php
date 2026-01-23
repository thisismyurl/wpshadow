<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_404 extends Diagnostic_Base {
  public static function check(): ?array {
    return ['id' => 'monitor-404_traffic_analysis', 'title' => __('404 Traffic Analysis', 'wpshadow'), 'description' => __('Tracks traffic to 404 pages. Indicates broken links, outdated URLs, or malformed requests.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }

}