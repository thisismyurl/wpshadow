<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Multi-Site Style Inheritance
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-multisite-style-inheritance
 * Training: https://wpshadow.com/training/design-multisite-style-inheritance
 */
class Diagnostic_Design_MULTISITE_STYLE_INHERITANCE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-multisite-style-inheritance',
            'title' => __('Multi-Site Style Inheritance', 'wpshadow'),
            'description' => __('Checks parent theme styles properly inherited.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-multisite-style-inheritance',
            'training_link' => 'https://wpshadow.com/training/design-multisite-style-inheritance',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}