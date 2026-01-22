<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Navigation Block Style
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-navigation-block-style
 * Training: https://wpshadow.com/training/design-navigation-block-style
 */
class Diagnostic_Design_DESIGN_NAVIGATION_BLOCK_STYLE {
    public static function check() {
        return [
            'id' => 'design-navigation-block-style',
            'title' => __('Navigation Block Style', 'wpshadow'),
            'description' => __('Checks nav block typography, spacing, and state consistency.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-navigation-block-style',
            'training_link' => 'https://wpshadow.com/training/design-navigation-block-style',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

