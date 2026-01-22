<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Semantic Token Usage
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-system-semantic-tokens
 * Training: https://wpshadow.com/training/design-system-semantic-tokens
 */
class Diagnostic_Design_SYSTEM_SEMANTIC_TOKENS {
    public static function check() {
        return [
            'id' => 'design-system-semantic-tokens',
            'title' => __('Semantic Token Usage', 'wpshadow'),
            'description' => __('Verifies semantic tokens used (primary-color) not literal (blue-500).', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-system-semantic-tokens',
            'training_link' => 'https://wpshadow.com/training/design-system-semantic-tokens',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
