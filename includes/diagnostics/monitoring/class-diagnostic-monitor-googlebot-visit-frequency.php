<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Googlebot_Visit_Frequency extends Diagnostic_Base {
    public static function check(): ?array {
        return ['id' => 'monitor-googlebot-frequency', 'title' => __('Googlebot Crawl Frequency Monitoring', 'wpshadow'), 'description' => __('Tracks how often Google crawls your site. Sudden drop = crawl issues. Increase = content quality signals detected.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/crawl-stats/', 'training_link' => 'https://wpshadow.com/training/search-console/', 'auto_fixable' => false, 'threat_level' => 5];
    }

}