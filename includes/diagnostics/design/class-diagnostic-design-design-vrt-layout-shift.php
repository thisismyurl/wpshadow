<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: VRT Layout Shift
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-vrt-layout-shift
 * Training: https://wpshadow.com/training/design-vrt-layout-shift
 */
class Diagnostic_Design_DESIGN_VRT_LAYOUT_SHIFT extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-vrt-layout-shift',
            'title' => __('VRT Layout Shift', 'wpshadow'),
            'description' => __('Detects layout shifts versus baseline in key regions.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-vrt-layout-shift',
            'training_link' => 'https://wpshadow.com/training/design-vrt-layout-shift',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}