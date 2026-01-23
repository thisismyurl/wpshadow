<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

class Diagnostic_Monitor_Device_Type_Shift extends Diagnostic_Base { public static function check(): ?array { return ['id' => 'monitor-device-shift', 'title' => __('Device Type Distribution Shift', 'wpshadow'), 'description' => __('Detects sudden changes in mobile/desktop/tablet split. Drop in mobile = responsiveness issue or mobile ranking loss.', 'wpshadow'), 'severity' => 'high', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/device-analytics/', 'training_link' => 'https://wpshadow.com/training/responsive-monitoring/', 'auto_fixable' => false, 'threat_level' => 7]; } 
}