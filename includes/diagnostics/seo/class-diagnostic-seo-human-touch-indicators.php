<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_SEO_Human_Touch_Indicators extends Diagnostic_Base {
    public static function check(): ?array {
        return ['id' => 'seo-human-touch-indicators', 'title' => __('Human Touch Indicators', 'wpshadow'), 'description' => __('Detects genuine human authorship markers: personal anecdotes, typos, contradictions, uncertain language, personality quirks. AI content lacks these.', 'wpshadow'), 'severity' => 'medium', 'category' => 'seo', 'kb_link' => 'https://wpshadow.com/kb/authentic-voice/', 'training_link' => 'https://wpshadow.com/training/personal-brand/', 'auto_fixable' => false, 'threat_level' => 7];
    }
}
