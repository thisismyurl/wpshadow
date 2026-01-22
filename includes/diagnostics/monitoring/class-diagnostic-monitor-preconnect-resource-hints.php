<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

class Diagnostic_Monitor_Preconnect_Resource_Hints extends Diagnostic_Base { public static function check(): ?array { return ['id' => 'monitor-preconnect', 'title' => __('Preconnect Resource Hints Status', 'wpshadow'), 'description' => __('Verifies preconnect to critical third-party domains. Saves 300-400ms by establishing connection early.', 'wpshadow'), 'severity' => 'low', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/preconnect/', 'training_link' => 'https://wpshadow.com/training/resource-hints/', 'auto_fixable' => false, 'threat_level' => 3]; } }
