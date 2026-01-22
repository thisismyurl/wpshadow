<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Border System Enforcement
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-border-system
 * Training: https://wpshadow.com/training/design-border-system
 */
class Diagnostic_Design_DESIGN_BORDER_SYSTEM extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-border-system',
            'title' => __('Border System Enforcement', 'wpshadow'),
            'description' => __('Enforces border widths and styles from the token set.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-border-system',
            'training_link' => 'https://wpshadow.com/training/design-border-system',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
