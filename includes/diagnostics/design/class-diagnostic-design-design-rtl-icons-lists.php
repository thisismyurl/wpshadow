<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: RTL Icons and Lists
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-rtl-icons-lists
 * Training: https://wpshadow.com/training/design-rtl-icons-lists
 */
class Diagnostic_Design_DESIGN_RTL_ICONS_LISTS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-rtl-icons-lists',
            'title' => __('RTL Icons and Lists', 'wpshadow'),
            'description' => __('Checks list indentation and icon mirroring in RTL.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-rtl-icons-lists',
            'training_link' => 'https://wpshadow.com/training/design-rtl-icons-lists',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
