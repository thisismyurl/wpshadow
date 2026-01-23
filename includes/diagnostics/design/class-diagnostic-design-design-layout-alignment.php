<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Layout Alignment
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-layout-alignment
 * Training: https://wpshadow.com/training/design-layout-alignment
 */
class Diagnostic_Design_DESIGN_LAYOUT_ALIGNMENT extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-layout-alignment',
            'title' => __('Layout Alignment', 'wpshadow'),
            'description' => __('Ensures blocks respect container widths and gaps without overflow.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-layout-alignment',
            'training_link' => 'https://wpshadow.com/training/design-layout-alignment',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}