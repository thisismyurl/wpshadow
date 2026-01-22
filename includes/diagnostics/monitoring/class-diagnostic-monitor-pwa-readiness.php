<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Pwa extends Diagnostic_Base {
  public static function check(): ?array {
    return ['id' => 'monitor-pwa_readiness', 'title' => __('Progressive Web App Readiness', 'wpshadow'), 'description' => __('Checks PWA requirements (manifest, SW, icon). Incomplete = missed mobile boost.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
