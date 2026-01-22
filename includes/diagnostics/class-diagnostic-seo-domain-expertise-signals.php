<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_SEO_Domain_Expertise_Signals {
    public static function check() {
        return ['id' => 'seo-expertise-signals', 'title' => __('Domain Expertise Signals', 'wpshadow'), 'description' => __('Detects insider knowledge markers: industry jargon, insider terminology, controversial takes, nuanced disagreements with mainstream. AI plays it safe and generic.', 'wpshadow'), 'severity' => 'high', 'category' => 'seo', 'kb_link' => 'https://wpshadow.com/kb/industry-authority/', 'training_link' => 'https://wpshadow.com/training/thought-leadership/', 'auto_fixable' => false, 'threat_level' => 8];
    }
}
