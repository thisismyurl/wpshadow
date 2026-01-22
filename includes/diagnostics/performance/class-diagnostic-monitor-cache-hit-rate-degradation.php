<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Cache_Hit_Rate_Degradation extends Diagnostic_Base .php {
    public static function check(): ?array {
        return ['id' => 'monitor-cache-hits', 'title' => __('Cache Hit Rate Degradation', 'wpshadow'), 'description' => __('Tracks percentage of requests served from cache. Drop indicates cache misconfiguration or thrashing.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/caching-strategy/', 'training_link' => 'https://wpshadow.com/training/cache-configuration/', 'auto_fixable' => false, 'threat_level' => 6];
    }
}
