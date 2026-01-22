<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_Service-worker-implementation {
  public static function check() {
    return ['id' => 'monitor-service_worker_implementation', 'title' => __('Service Worker Implementation', 'wpshadow'), 'description' => __('Verifies service worker enables offline. Missing = lost offline functionality.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
