<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

class Diagnostic_Monitor_Cookie_Consent_Rate extends Diagnostic_Base { public static function check(): ?array { return ['id' => 'monitor-consent-rate', 'title' => __('Cookie Consent Acceptance Rate', 'wpshadow'), 'description' => __('Tracks % accepting vs rejecting cookies. Low acceptance = UX friction or aggressive banner design.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/consent-optimization/', 'training_link' => 'https://wpshadow.com/training/banner-design/', 'auto_fixable' => false, 'threat_level' => 5]; } 
}