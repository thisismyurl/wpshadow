<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_Email_Deliverability_Issues.php {
    public static function check() {
        return ['id' => 'monitor-email-delivery', 'title' => __('Email Deliverability Monitoring', 'wpshadow'), 'description' => __('Tracks email bounces, failures, spam flagging. Ensures password resets, notifications reach users.', 'wpshadow'), 'severity' => 'high', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/email-setup/', 'training_link' => 'https://wpshadow.com/training/smtp-configuration/', 'auto_fixable' => false, 'threat_level' => 7];
    }
}
