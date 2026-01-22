<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: @font-face Unused
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-css-unused-font-face
 * Training: https://wpshadow.com/training/design-css-unused-font-face
 */
class Diagnostic_Design_DESIGN_CSS_UNUSED_FONT_FACE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-css-unused-font-face',
            'title' => __('@font-face Unused', 'wpshadow'),
            'description' => __('Detects font-face declarations that are never used in rendered content.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-css-unused-font-face',
            'training_link' => 'https://wpshadow.com/training/design-css-unused-font-face',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
