<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Font Variant Bloat
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-font-variant-bloat
 * Training: https://wpshadow.com/training/design-font-variant-bloat
 */
class Diagnostic_Design_DESIGN_FONT_VARIANT_BLOAT extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-font-variant-bloat',
            'title' => __('Font Variant Bloat', 'wpshadow'),
            'description' => __('Counts loaded font weights/styles versus actual usage.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-font-variant-bloat',
            'training_link' => 'https://wpshadow.com/training/design-font-variant-bloat',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
