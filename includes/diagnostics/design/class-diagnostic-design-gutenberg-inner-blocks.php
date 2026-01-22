<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Inner Blocks Structure
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-gutenberg-inner-blocks
 * Training: https://wpshadow.com/training/design-gutenberg-inner-blocks
 */
class Diagnostic_Design_GUTENBERG_INNER_BLOCKS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-gutenberg-inner-blocks',
            'title' => __('Inner Blocks Structure', 'wpshadow'),
            'description' => __('Validates inner block nesting, hierarchy, responsive behavior.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-gutenberg-inner-blocks',
            'training_link' => 'https://wpshadow.com/training/design-gutenberg-inner-blocks',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
