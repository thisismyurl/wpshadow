<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Longword Handling
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-longword-handling
 * Training: https://wpshadow.com/training/design-longword-handling
 */
class Diagnostic_Design_DESIGN_LONGWORD_HANDLING extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-longword-handling',
            'title' => __('Longword Handling', 'wpshadow'),
            'description' => __('Checks hyphenation and wrapping for long words.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-longword-handling',
            'training_link' => 'https://wpshadow.com/training/design-longword-handling',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
