<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: RTL Margin/Padding Mirroring
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-rtl-margin-padding
 * Training: https://wpshadow.com/training/design-rtl-margin-padding
 */
class Diagnostic_Design_RTL_MARGIN_PADDING extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-rtl-margin-padding',
            'title' => __('RTL Margin/Padding Mirroring', 'wpshadow'),
            'description' => __('Checks margins/padding properly mirrored for RTL.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-rtl-margin-padding',
            'training_link' => 'https://wpshadow.com/training/design-rtl-margin-padding',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}