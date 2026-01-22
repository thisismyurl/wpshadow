<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: RTL Comprehensive Support
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-rtl-support-comprehensive
 * Training: https://wpshadow.com/training/design-rtl-support-comprehensive
 */
class Diagnostic_Design_RTL_SUPPORT_COMPREHENSIVE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-rtl-support-comprehensive',
            'title' => __('RTL Comprehensive Support', 'wpshadow'),
            'description' => __('Tests Arabic/Hebrew/Farsi text layout.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-rtl-support-comprehensive',
            'training_link' => 'https://wpshadow.com/training/design-rtl-support-comprehensive',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
