<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Component Conformance Audit
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-component-conformance
 * Training: https://wpshadow.com/training/design-component-conformance
 */
class Diagnostic_Design_DESIGN_COMPONENT_CONFORMANCE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-component-conformance',
            'title' => __('Component Conformance Audit', 'wpshadow'),
            'description' => __('Detects rogue components diverging from canonical styles.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-component-conformance',
            'training_link' => 'https://wpshadow.com/training/design-component-conformance',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}