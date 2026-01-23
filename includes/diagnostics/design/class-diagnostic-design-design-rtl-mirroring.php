<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: RTL Mirroring
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-rtl-mirroring
 * Training: https://wpshadow.com/training/design-rtl-mirroring
 */
class Diagnostic_Design_DESIGN_RTL_MIRRORING extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-rtl-mirroring',
            'title' => __('RTL Mirroring', 'wpshadow'),
            'description' => __('Checks mirrored layouts, icon direction, and alignment.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-rtl-mirroring',
            'training_link' => 'https://wpshadow.com/training/design-rtl-mirroring',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}