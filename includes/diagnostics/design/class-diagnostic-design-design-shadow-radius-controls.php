<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Shadow and Radius Controls
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-shadow-radius-controls
 * Training: https://wpshadow.com/training/design-shadow-radius-controls
 */
class Diagnostic_Design_DESIGN_SHADOW_RADIUS_CONTROLS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-shadow-radius-controls',
            'title' => __('Shadow and Radius Controls', 'wpshadow'),
            'description' => __('Checks controls map to shadow and radius scales.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-shadow-radius-controls',
            'training_link' => 'https://wpshadow.com/training/design-shadow-radius-controls',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}