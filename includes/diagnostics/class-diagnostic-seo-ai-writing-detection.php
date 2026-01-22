<?php
declare(strict_types=1);

/**
 * Diagnostic: AI Writing Detection (ChatGPT, Claude, Gemini)
 * Philosophy: Detect AI-generated content that lacks human authenticity and may be penalized by Google's helpful content update
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_AI_Writing_Detection {
    public static function check() {
        return [
            'id' => 'seo-ai-writing-detection',
            'title' => __('AI Writing Detection', 'wpshadow'),
            'description' => __('Analyzes content for statistical patterns indicating AI generation (ChatGPT, Claude, Gemini). Google\'s helpful content update penalizes purely AI content without human review.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/ai-content-authenticity/',
            'training_link' => 'https://wpshadow.com/training/ai-content-strategy/',
            'auto_fixable' => false,
            'threat_level' => 6,
        ];
    }
}
