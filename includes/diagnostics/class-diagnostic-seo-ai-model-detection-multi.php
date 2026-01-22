<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_SEO_AI_Model_Detection_Multi {
    public static function check() {
        return ['id' => 'seo-ai-model-detection', 'title' => __('AI Model Detection (ChatGPT/Claude/Gemini)', 'wpshadow'), 'description' => __('Identifies which AI model likely generated content based on stylistic fingerprints. ChatGPT uses different patterns than Claude vs Gemini.', 'wpshadow'), 'severity' => 'medium', 'category' => 'seo', 'kb_link' => 'https://wpshadow.com/kb/ai-model-signatures/', 'training_link' => 'https://wpshadow.com/training/ai-identification/', 'auto_fixable' => false, 'threat_level' => 5];
    }
}
