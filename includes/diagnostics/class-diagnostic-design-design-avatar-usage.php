<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Avatar Usage Consistency
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-avatar-usage
 * Training: https://wpshadow.com/training/design-avatar-usage
 */
class Diagnostic_Design_DESIGN_AVATAR_USAGE {
    public static function check() {
        return [
            'id' => 'design-avatar-usage',
            'title' => __('Avatar Usage Consistency', 'wpshadow'),
            'description' => __('Checks avatar sizes, radii, and borders consistency.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-avatar-usage',
            'training_link' => 'https://wpshadow.com/training/design-avatar-usage',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

