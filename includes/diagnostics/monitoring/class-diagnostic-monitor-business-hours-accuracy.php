<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

class Diagnostic_Monitor_Business_Hours_Accuracy extends Diagnostic_Base { public static function check(): ?array { return ['id' => 'monitor-hours-accuracy', 'title' => __('Business Hours Accuracy Monitoring', 'wpshadow'), 'description' => __('Verifies hours listed correctly across Google, website, directories. Outdated hours = lost customers.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/business-info/', 'training_link' => 'https://wpshadow.com/training/gmb-optimization/', 'auto_fixable' => false, 'threat_level' => 5]; } }
