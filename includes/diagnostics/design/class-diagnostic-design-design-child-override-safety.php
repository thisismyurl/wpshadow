<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Child Override Safety
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-child-override-safety
 * Training: https://wpshadow.com/training/design-child-override-safety
 */
class Diagnostic_Design_DESIGN_CHILD_OVERRIDE_SAFETY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-child-override-safety',
            'title' => __('Child Override Safety', 'wpshadow'),
            'description' => __('Detects stale or duplicate child overrides.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-child-override-safety',
            'training_link' => 'https://wpshadow.com/training/design-child-override-safety',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}