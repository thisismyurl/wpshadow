<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: PostMessage Coverage
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-postmessage-coverage
 * Training: https://wpshadow.com/training/design-postmessage-coverage
 */
class Diagnostic_Design_DESIGN_POSTMESSAGE_COVERAGE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-postmessage-coverage',
            'title' => __('PostMessage Coverage', 'wpshadow'),
            'description' => __('Checks live preview coverage for customizer controls.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-postmessage-coverage',
            'training_link' => 'https://wpshadow.com/training/design-postmessage-coverage',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}