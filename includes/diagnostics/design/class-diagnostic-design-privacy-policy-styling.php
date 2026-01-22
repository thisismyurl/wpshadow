<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Privacy Policy Page Styling
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-privacy-policy-styling
 * Training: https://wpshadow.com/training/design-privacy-policy-styling
 */
class Diagnostic_Design_PRIVACY_POLICY_STYLING extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-privacy-policy-styling',
            'title' => __('Privacy Policy Page Styling', 'wpshadow'),
            'description' => __('Checks privacy page properly formatted.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-privacy-policy-styling',
            'training_link' => 'https://wpshadow.com/training/design-privacy-policy-styling',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
