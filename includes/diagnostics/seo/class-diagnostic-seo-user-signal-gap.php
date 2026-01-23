<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_SEO_User_Signal_Gap extends Diagnostic_Base {
    public static function check(): ?array {
        return ['id' => 'seo-user-signal-gap', 'title' => __('User Engagement Signal Gap', 'wpshadow'), 'description' => __('Compares estimated user engagement: time on page, scroll depth, click-through rate signals. If competitors have higher CTR from SERPs, you\'re losing visibility.', 'wpshadow'), 'severity' => 'medium', 'category' => 'seo', 'kb_link' => 'https://wpshadow.com/kb/ctr-optimization/', 'training_link' => 'https://wpshadow.com/training/serp-titles/', 'auto_fixable' => false, 'threat_level' => 6];
    }

}