<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Opacity Scale Enforcement
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-system-opacity-scale
 * Training: https://wpshadow.com/training/design-system-opacity-scale
 */
class Diagnostic_Design_SYSTEM_OPACITY_SCALE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-system-opacity-scale',
            'title' => __('Opacity Scale Enforcement', 'wpshadow'),
            'description' => __('Confirms opacity values use design system scale (10%, 30%, 50%).', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-system-opacity-scale',
            'training_link' => 'https://wpshadow.com/training/design-system-opacity-scale',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
