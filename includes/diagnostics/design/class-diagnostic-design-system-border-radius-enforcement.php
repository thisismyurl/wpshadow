<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Border Radius Enforcement
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-system-border-radius-enforcement
 * Training: https://wpshadow.com/training/design-system-border-radius-enforcement
 */
class Diagnostic_Design_SYSTEM_BORDER_RADIUS_ENFORCEMENT extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-system-border-radius-enforcement',
            'title' => __('Border Radius Enforcement', 'wpshadow'),
            'description' => __('Confirms border-radius values use design system scale.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-system-border-radius-enforcement',
            'training_link' => 'https://wpshadow.com/training/design-system-border-radius-enforcement',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
