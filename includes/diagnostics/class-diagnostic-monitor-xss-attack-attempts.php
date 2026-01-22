<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_XSS_Attack_Attempts {
    public static function check() {
        return ['id' => 'monitor-xss-attacks', 'title' => __('Cross-Site Scripting (XSS) Attempts', 'wpshadow'), 'description' => __('Detects XSS payloads (<script>, onload=, javascript:). Blocks before malicious JS runs on visitor browsers.', 'wpshadow'), 'severity' => 'critical', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/xss-prevention/', 'training_link' => 'https://wpshadow.com/training/input-sanitization/', 'auto_fixable' => false, 'threat_level' => 10];
    }
}
