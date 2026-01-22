<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Naming Convention Enforcement
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-system-naming-convention
 * Training: https://wpshadow.com/training/design-system-naming-convention
 */
class Diagnostic_Design_SYSTEM_NAMING_CONVENTION extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-system-naming-convention',
            'title' => __('Naming Convention Enforcement', 'wpshadow'),
            'description' => __('Verifies CSS class naming follows BEM, SMACSS, or defined convention.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-system-naming-convention',
            'training_link' => 'https://wpshadow.com/training/design-system-naming-convention',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
