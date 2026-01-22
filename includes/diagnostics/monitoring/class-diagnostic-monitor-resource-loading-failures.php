<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

class Diagnostic_Monitor_Resource_Loading_Failures extends Diagnostic_Base { public static function check(): ?array { return ['id' => 'monitor-resource-failures', 'title' => __('Resource Loading Failure Rate', 'wpshadow'), 'description' => __('Tracks CSS, JS, image loading failures. Increases indicate CDN issues or 3rd-party service failures.', 'wpshadow'), 'severity' => 'high', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/asset-loading/', 'training_link' => 'https://wpshadow.com/training/resource-optimization/', 'auto_fixable' => false, 'threat_level' => 7]; } }
