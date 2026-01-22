<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_Cache_Hit_Rate_Degradation.php {
    public static function check() {
        return ['id' => 'monitor-cache-hits', 'title' => __('Cache Hit Rate Degradation', 'wpshadow'), 'description' => __('Tracks percentage of requests served from cache. Drop indicates cache misconfiguration or thrashing.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/caching-strategy/', 'training_link' => 'https://wpshadow.com/training/cache-configuration/', 'auto_fixable' => false, 'threat_level' => 6];
    }
}
