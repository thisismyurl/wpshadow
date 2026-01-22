<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

class Diagnostic_Monitor_Geographic_Traffic_Anomaly extends Diagnostic_Base { public static function check(): ?array { return ['id' => 'monitor-geo-anomaly', 'title' => __('Geographic Traffic Anomaly', 'wpshadow'), 'description' => __('Detects unexpected changes in traffic by geography. Indicates regional server issues or targeted attacks.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/geo-targeting/', 'training_link' => 'https://wpshadow.com/training/international-seo/', 'auto_fixable' => false, 'threat_level' => 5]; } }
