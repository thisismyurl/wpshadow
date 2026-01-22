<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: RTL Flexbox Direction
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-rtl-flex-direction
 * Training: https://wpshadow.com/training/design-rtl-flex-direction
 */
class Diagnostic_Design_RTL_FLEX_DIRECTION extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-rtl-flex-direction',
            'title' => __('RTL Flexbox Direction', 'wpshadow'),
            'description' => __('Confirms flex-direction:row-reverse used appropriately.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-rtl-flex-direction',
            'training_link' => 'https://wpshadow.com/training/design-rtl-flex-direction',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
