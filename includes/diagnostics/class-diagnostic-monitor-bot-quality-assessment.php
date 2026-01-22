<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_Bot_Quality_Assessment {
    public static function check() {
        return ['id' => 'monitor-bot-quality', 'title' => __('Bot Traffic Quality Assessment', 'wpshadow'), 'description' => __('Distinguishes good bots (Google, Bing) from bad (scrapers, scrapers). Measures % legitimate bot traffic vs junk.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/bot-analytics/', 'training_link' => 'https://wpshadow.com/training/traffic-quality/', 'auto_fixable' => false, 'threat_level' => 4];
    }
}
