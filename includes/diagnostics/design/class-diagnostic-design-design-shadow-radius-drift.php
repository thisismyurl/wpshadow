<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Shadow & Radius Drift
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-shadow-radius-drift
 * Training: https://wpshadow.com/training/design-shadow-radius-drift
 */
class Diagnostic_Design_DESIGN_SHADOW_RADIUS_DRIFT extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-shadow-radius-drift',
            'title' => __('Shadow & Radius Drift', 'wpshadow'),
            'description' => __('Detects unauthorized shadows or radii versus token scales.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-shadow-radius-drift',
            'training_link' => 'https://wpshadow.com/training/design-shadow-radius-drift',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
