<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

class Diagnostic_Monitor_Browser_Caching_Headers extends Diagnostic_Base { public static function check(): ?array { return ['id' => 'monitor-cache-headers', 'title' => __('Browser Caching Header Verification', 'wpshadow'), 'description' => __('Verifies Cache-Control headers set correctly. Missing headers = browser fetches fresh every time.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/cache-headers/', 'training_link' => 'https://wpshadow.com/training/caching-headers/', 'auto_fixable' => false, 'threat_level' => 5]; } 
}