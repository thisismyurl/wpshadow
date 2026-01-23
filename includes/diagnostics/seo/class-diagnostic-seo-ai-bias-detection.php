<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_SEO_AI_Bias_Detection extends Diagnostic_Base {
    public static function check(): ?array {
        return ['id' => 'seo-ai-bias-detection', 'title' => __('AI Bias Detection', 'wpshadow'), 'description' => __('Detects when AI training bias creates problematic generalizations, false equivalencies, or missing nuance. Content appears "safe" but lacks critical thinking—red flag for low expertise.', 'wpshadow'), 'severity' => 'medium', 'category' => 'seo', 'kb_link' => 'https://wpshadow.com/kb/balanced-perspective/', 'training_link' => 'https://wpshadow.com/training/critical-thinking/', 'auto_fixable' => false, 'threat_level' => 5];
    }

}