<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: RTL Border Radius
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-rtl-border-radius
 * Training: https://wpshadow.com/training/design-rtl-border-radius
 */
class Diagnostic_Design_RTL_BORDER_RADIUS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-rtl-border-radius',
            'title' => __('RTL Border Radius', 'wpshadow'),
            'description' => __('Validates border-radius correctly applied in RTL.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-rtl-border-radius',
            'training_link' => 'https://wpshadow.com/training/design-rtl-border-radius',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
