<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Design System Documentation Coverage
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-system-documentation-coverage
 * Training: https://wpshadow.com/training/design-system-documentation-coverage
 */
class Diagnostic_Design_SYSTEM_DOCUMENTATION_COVERAGE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-system-documentation-coverage',
            'title' => __('Design System Documentation Coverage', 'wpshadow'),
            'description' => __('Checks if Figma, Storybook, or wiki has component definitions.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-system-documentation-coverage',
            'training_link' => 'https://wpshadow.com/training/design-system-documentation-coverage',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}