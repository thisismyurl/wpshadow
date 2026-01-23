<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_SEO_Content_Entropy_Analysis extends Diagnostic_Base {
    public static function check(): ?array {
        return ['id' => 'seo-content-entropy', 'title' => __('Content Entropy Analysis', 'wpshadow'), 'description' => __('Measures information density and randomness. AI generates low-entropy (predictable) text. Human experts generate high-entropy (informative) content.', 'wpshadow'), 'severity' => 'medium', 'category' => 'seo', 'kb_link' => 'https://wpshadow.com/kb/content-depth/', 'training_link' => 'https://wpshadow.com/training/expertise-signals/', 'auto_fixable' => false, 'threat_level' => 6];
    }

}