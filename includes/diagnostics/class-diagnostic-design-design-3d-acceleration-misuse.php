<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: 3D Acceleration Misuse
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-3d-acceleration-misuse
 * Training: https://wpshadow.com/training/design-3d-acceleration-misuse
 */
class Diagnostic_Design_DESIGN_3D_ACCELERATION_MISUSE {
    public static function check() {
        return [
            'id' => 'design-3d-acceleration-misuse',
            'title' => __('3D Acceleration Misuse', 'wpshadow'),
            'description' => __('Flags unnecessary translateZ or 3D hacks.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-3d-acceleration-misuse',
            'training_link' => 'https://wpshadow.com/training/design-3d-acceleration-misuse',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

