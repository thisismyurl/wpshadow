<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Redirect extends Diagnostic_Base {
  public static function check(): ?array {
        // Check if monitoring plugins are active
        $has_monitoring = is_plugin_active('wordfence/wordfence.php') || 
                         is_plugin_active('sucuri-scanner/sucuri.php');
        if ($has_monitoring) {
            return null; // Monitoring in place
        }
        
return ['id' => 'monitor-redirect_effectiveness', 'title' => __('Redirect Effectiveness Score', 'wpshadow'), 'description' => __('Measures how many old URLs properly redirect. Orphaned URLs = lost link equity.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }

}