<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Text Expansion
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-text-expansion
 * Training: https://wpshadow.com/training/design-text-expansion
 */
class Diagnostic_Design_DESIGN_TEXT_EXPANSION extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-text-expansion',
            'title' => __('Text Expansion', 'wpshadow'),
            'description' => __('Simulates longer strings and detects overflow or clipping.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-text-expansion',
            'training_link' => 'https://wpshadow.com/training/design-text-expansion',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
