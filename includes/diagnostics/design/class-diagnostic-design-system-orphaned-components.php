<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Orphaned Components Detection
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-system-orphaned-components
 * Training: https://wpshadow.com/training/design-system-orphaned-components
 */
class Diagnostic_Design_SYSTEM_ORPHANED_COMPONENTS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-system-orphaned-components',
            'title' => __('Orphaned Components Detection', 'wpshadow'),
            'description' => __('Finds components in code but not in design system (technical debt).', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-system-orphaned-components',
            'training_link' => 'https://wpshadow.com/training/design-system-orphaned-components',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
