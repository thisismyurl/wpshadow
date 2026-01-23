<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: VRT Component Regressions
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-vrt-component-regressions
 * Training: https://wpshadow.com/training/design-vrt-component-regressions
 */
class Diagnostic_Design_DESIGN_VRT_COMPONENT_REGRESSIONS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-vrt-component-regressions',
            'title' => __('VRT Component Regressions', 'wpshadow'),
            'description' => __('Captures per-component snapshot diffs for regressions.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-vrt-component-regressions',
            'training_link' => 'https://wpshadow.com/training/design-vrt-component-regressions',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}