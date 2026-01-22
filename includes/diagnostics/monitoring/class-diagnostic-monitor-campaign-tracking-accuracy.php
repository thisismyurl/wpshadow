<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Campaign extends Diagnostic_Base {
  public static function check(): ?array {
    return ['id' => 'monitor-campaign_tracking_accuracy', 'title' => __('Campaign Tracking Accuracy', 'wpshadow'), 'description' => __('Verifies campaigns tracked correctly. Broken tracking = invisible ROI.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
