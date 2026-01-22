<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_SEO_Uncertainty_Language_Markers {
    public static function check() {
        return ['id' => 'seo-uncertainty-markers', 'title' => __('Uncertainty Language Markers', 'wpshadow'), 'description' => __('Detects excessive use of hedging language ("may", "might", "could", "seems"). AI avoids commitment. Experts make confident claims backed by evidence.', 'wpshadow'), 'severity' => 'low', 'category' => 'seo', 'kb_link' => 'https://wpshadow.com/kb/confident-writing/', 'training_link' => 'https://wpshadow.com/training/persuasive-copy/', 'auto_fixable' => false, 'threat_level' => 4];
    }
}
