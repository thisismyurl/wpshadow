<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_SEO_Personal_Brand_Signals {
    public static function check() {
        return ['id' => 'seo-personal-brand-signals', 'title' => __('Personal Brand Signals', 'wpshadow'), 'description' => __('Detects distinctive personal brand elements: catchphrases, unique perspectives, recurring themes, signature style. AI content is generic, replaceable, forgettable.', 'wpshadow'), 'severity' => 'medium', 'category' => 'seo', 'kb_link' => 'https://wpshadow.com/kb/brand-identity/', 'training_link' => 'https://wpshadow.com/training/personal-brand/', 'auto_fixable' => false, 'threat_level' => 6];
    }
}
