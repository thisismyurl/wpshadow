<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_SQL_Injection_Attempts {
    public static function check() {
        return ['id' => 'monitor-sql-injection', 'title' => __('SQL Injection Attack Attempts', 'wpshadow'), 'description' => __('Detects SQL injection patterns in requests (\' OR 1=1, UNION SELECT, etc). Blocks attacker reconnaissance before exploit.', 'wpshadow'), 'severity' => 'critical', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/sql-injection-prevention/', 'training_link' => 'https://wpshadow.com/training/security-hardening/', 'auto_fixable' => false, 'threat_level' => 10];
    }
}
