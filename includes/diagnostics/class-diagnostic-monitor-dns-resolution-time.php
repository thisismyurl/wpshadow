<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_DNS_Resolution_Time { public static function check() { return ['id' => 'monitor-dns-time', 'title' => __('DNS Resolution Time Monitoring', 'wpshadow'), 'description' => __('Tracks DNS query time. Slow DNS = TTFB impact, ranking penalty. Indicates DNS provider issues.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/dns-optimization/', 'training_link' => 'https://wpshadow.com/training/dns-setup/', 'auto_fixable' => false, 'threat_level' => 5]; } }
