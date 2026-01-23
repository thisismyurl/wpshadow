<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: VRT Breakpoints Coverage
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-vrt-breakpoints
 * Training: https://wpshadow.com/training/design-vrt-breakpoints
 */
class Diagnostic_Design_DESIGN_VRT_BREAKPOINTS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-vrt-breakpoints',
            'title' => __('VRT Breakpoints Coverage', 'wpshadow'),
            'description' => __('Captures mobile, tablet, desktop, and ultra-wide baselines.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-vrt-breakpoints',
            'training_link' => 'https://wpshadow.com/training/design-vrt-breakpoints',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}