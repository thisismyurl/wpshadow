<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

class Diagnostic_Monitor_GDPR_Consent_Tracking extends Diagnostic_Base { public static function check(): ?array { return ['id' => 'monitor-gdpr-consent', 'title' => __('GDPR Consent Tracking Verification', 'wpshadow'), 'description' => __('Verifies consent banner fires, consent stored correctly. Tracking without consent = legal violation.', 'wpshadow'), 'severity' => 'critical', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/gdpr-compliance/', 'training_link' => 'https://wpshadow.com/training/consent-management/', 'auto_fixable' => false, 'threat_level' => 10]; } 
}