<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_Admin_Panel_Access {
    public static function check() {
        return ['id' => 'monitor-admin-access', 'title' => __('Admin Panel Access Status', 'wpshadow'), 'description' => __('Verifies /wp-admin is accessible and responsive. Inaccessible admin = operational blindness, can\'t fix problems.', 'wpshadow'), 'severity' => 'critical', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/admin-access/', 'training_link' => 'https://wpshadow.com/training/troubleshooting/', 'auto_fixable' => false, 'threat_level' => 10];
    }
}
