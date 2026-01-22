<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Admin_Panel_Access extends Diagnostic_Base {
    public static function check(): ?array {
        return ['id' => 'monitor-admin-access', 'title' => __('Admin Panel Access Status', 'wpshadow'), 'description' => __('Verifies /wp-admin is accessible and responsive. Inaccessible admin = operational blindness, can\'t fix problems.', 'wpshadow'), 'severity' => 'critical', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/admin-access/', 'training_link' => 'https://wpshadow.com/training/troubleshooting/', 'auto_fixable' => false, 'threat_level' => 10];
    }
}
