<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

class Diagnostic_Monitor_Social_Media_Traffic_Trend extends Diagnostic_Base { public static function check(): ?array { return ['id' => 'monitor-social-trend', 'title' => __('Social Media Traffic Trend', 'wpshadow'), 'description' => __('Monitors traffic from social platforms. Changes indicate social strategy effectiveness or share volume changes.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/social-analytics/', 'training_link' => 'https://wpshadow.com/training/social-strategy/', 'auto_fixable' => false, 'threat_level' => 4]; } }
