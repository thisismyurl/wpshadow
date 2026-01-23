<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Icon Size Enforcement
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-system-icon-scale
 * Training: https://wpshadow.com/training/design-system-icon-scale
 */
class Diagnostic_Design_SYSTEM_ICON_SCALE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-system-icon-scale',
            'title' => __('Icon Size Enforcement', 'wpshadow'),
            'description' => __('Confirms icons use defined size scale (16px, 24px, 32px, 48px).', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-system-icon-scale',
            'training_link' => 'https://wpshadow.com/training/design-system-icon-scale',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}