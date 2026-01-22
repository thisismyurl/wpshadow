<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Component Dependency Graph
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-system-component-dependencies
 * Training: https://wpshadow.com/training/design-system-component-dependencies
 */
class Diagnostic_Design_SYSTEM_COMPONENT_DEPENDENCIES extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-system-component-dependencies',
            'title' => __('Component Dependency Graph', 'wpshadow'),
            'description' => __('Maps component dependencies, detects circular references.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-system-component-dependencies',
            'training_link' => 'https://wpshadow.com/training/design-system-component-dependencies',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
