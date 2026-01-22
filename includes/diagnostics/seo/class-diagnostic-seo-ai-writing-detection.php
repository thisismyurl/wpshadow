<?php
declare(strict_types=1);
/**
 * Diagnostic: AI Writing Detection (ChatGPT, Claude, Gemini)
 * Philosophy: Detect AI-generated content that lacks human authenticity and may be penalized by Google's helpful content update
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_AI_Writing_Detection extends Diagnostic_Base {
    public static function check(): ?array {
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
