<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Palette Parity
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-palette-parity
 * Training: https://wpshadow.com/training/design-palette-parity
 */
class Diagnostic_Design_DESIGN_PALETTE_PARITY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-palette-parity',
            'title' => __('Palette Parity', 'wpshadow'),
            'description' => __('Checks editor palette matches front-end and tokens.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-palette-parity',
            'training_link' => 'https://wpshadow.com/training/design-palette-parity',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}