<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_SEO_Contextual_Nuance_Index extends Diagnostic_Base {
    public static function check(): ?array {
        return ['id' => 'seo-contextual-nuance', 'title' => __('Contextual Nuance Index', 'wpshadow'), 'description' => __('Detects content that acknowledges context, exceptions, edge cases, and "it depends" answers. AI gives universal advice. Experts understand nuance.', 'wpshadow'), 'severity' => 'high', 'category' => 'seo', 'kb_link' => 'https://wpshadow.com/kb/nuanced-advice/', 'training_link' => 'https://wpshadow.com/training/expert-analysis/', 'auto_fixable' => false, 'threat_level' => 7];
    }
}
