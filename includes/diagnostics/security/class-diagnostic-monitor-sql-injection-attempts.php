<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_SQL_Injection_Attempts extends Diagnostic_Base {
    public static function check(): ?array {
        return ['id' => 'monitor-sql-injection', 'title' => __('SQL Injection Attack Attempts', 'wpshadow'), 'description' => __('Detects SQL injection patterns in requests (\' OR 1=1, UNION SELECT, etc). Blocks attacker reconnaissance before exploit.', 'wpshadow'), 'severity' => 'critical', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/sql-injection-prevention/', 'training_link' => 'https://wpshadow.com/training/security-hardening/', 'auto_fixable' => false, 'threat_level' => 10];
    }
}
