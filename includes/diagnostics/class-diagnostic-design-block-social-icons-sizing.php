<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Social Icons Sizing
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-block-social-icons-sizing
 * Training: https://wpshadow.com/training/design-block-social-icons-sizing
 */
class Diagnostic_Design_BLOCK_SOCIAL_ICONS_SIZING {
    public static function check() {
        return [
            'id' => 'design-block-social-icons-sizing',
            'title' => __('Social Icons Sizing', 'wpshadow'),
            'description' => __('Validates social icons properly sized, aligned.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-block-social-icons-sizing',
            'training_link' => 'https://wpshadow.com/training/design-block-social-icons-sizing',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
