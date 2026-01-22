<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

class Diagnostic_Monitor_CCPA_Cookie_Disclosure extends Diagnostic_Base { public static function check(): ?array { return ['id' => 'monitor-ccpa-disclosure', 'title' => __('CCPA Cookie Disclosure Compliance', 'wpshadow'), 'description' => __('Verifies CCPA disclosure visible to California users. Missing = $2500+ per violation fine.', 'wpshadow'), 'severity' => 'critical', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/ccpa-compliance/', 'training_link' => 'https://wpshadow.com/training/privacy-regulations/', 'auto_fixable' => false, 'threat_level' => 10]; } }
