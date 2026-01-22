<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Component System Violations
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-system-component-violations
 * Training: https://wpshadow.com/training/design-system-component-violations
 */
class Diagnostic_Design_SYSTEM_COMPONENT_VIOLATIONS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-system-component-violations',
            'title' => __('Component System Violations', 'wpshadow'),
            'description' => __('Detects components not using base classes, detects rogue implementations.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-system-component-violations',
            'training_link' => 'https://wpshadow.com/training/design-system-component-violations',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
