<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Will-Change Usage
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-will-change-usage
 * Training: https://wpshadow.com/training/design-will-change-usage
 */
class Diagnostic_Design_DESIGN_WILL_CHANGE_USAGE {
    public static function check() {
        return [
            'id' => 'design-will-change-usage',
            'title' => __('Will-Change Usage', 'wpshadow'),
            'description' => __('Checks appropriate will-change usage on animated elements.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-will-change-usage',
            'training_link' => 'https://wpshadow.com/training/design-will-change-usage',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

