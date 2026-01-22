<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_WordPress_Core_Update extends Diagnostic_Base .php {
    public static function check(): ?array {
        return ['id' => 'monitor-wp-updates', 'title' => __('WordPress Core Update Available', 'wpshadow'), 'description' => __('Alerts when WordPress major/minor/patch updates available. Delays increase security risk and incompatibility.', 'wpshadow'), 'severity' => 'high', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/wordpress-updates/', 'training_link' => 'https://wpshadow.com/training/core-upgrades/', 'auto_fixable' => false, 'threat_level' => 8];
    }
}
