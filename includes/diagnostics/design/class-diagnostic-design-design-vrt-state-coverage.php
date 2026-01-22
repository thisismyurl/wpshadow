<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: VRT State Coverage
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-vrt-state-coverage
 * Training: https://wpshadow.com/training/design-vrt-state-coverage
 */
class Diagnostic_Design_DESIGN_VRT_STATE_COVERAGE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-vrt-state-coverage',
            'title' => __('VRT State Coverage', 'wpshadow'),
            'description' => __('Captures hover, focus, active, disabled, and skeleton states for baselines.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-vrt-state-coverage',
            'training_link' => 'https://wpshadow.com/training/design-vrt-state-coverage',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
