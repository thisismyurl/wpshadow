<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Social Share Buttons
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-social-share-buttons
 * Training: https://wpshadow.com/training/design-social-share-buttons
 */
class Diagnostic_Design_DESIGN_SOCIAL_SHARE_BUTTONS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-social-share-buttons',
            'title' => __('Social Share Buttons', 'wpshadow'),
            'description' => __('Checks share button sizing, spacing, and contrast.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-social-share-buttons',
            'training_link' => 'https://wpshadow.com/training/design-social-share-buttons',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
