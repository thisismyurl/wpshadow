<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_SEO_AI_Training_Data_Leakage extends Diagnostic_Base {
    public static function check(): ?array {
        return ['id' => 'seo-ai-training-leakage', 'title' => __('AI Training Data Leakage Patterns', 'wpshadow'), 'description' => __('Detects phrases and examples common in AI training data (Wikipedia, Common Crawl). Repeated verbatim sentences indicate AI generation without revision.', 'wpshadow'), 'severity' => 'high', 'category' => 'seo', 'kb_link' => 'https://wpshadow.com/kb/original-content/', 'training_link' => 'https://wpshadow.com/training/plagiarism-prevention/', 'auto_fixable' => false, 'threat_level' => 8];
    }

}