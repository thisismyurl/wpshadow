<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Text Expansion Testing
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-text-expansion
 * Training: https://wpshadow.com/training/design-text-expansion
 */
class Diagnostic_Design_TEXT_EXPANSION {
    public static function check() {
        return [
            'id' => 'design-text-expansion',
            'title' => __('Text Expansion Testing', 'wpshadow'),
            'description' => __('Tests German/Finnish long text without overflow.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-text-expansion',
            'training_link' => 'https://wpshadow.com/training/design-text-expansion',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
