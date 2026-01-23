<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Custom Block Style Drift
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-custom-block-style-drift
 * Training: https://wpshadow.com/training/design-custom-block-style-drift
 */
class Diagnostic_Design_DESIGN_CUSTOM_BLOCK_STYLE_DRIFT extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-custom-block-style-drift',
            'title' => __('Custom Block Style Drift', 'wpshadow'),
            'description' => __('Ensures custom block styles align to tokens.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-custom-block-style-drift',
            'training_link' => 'https://wpshadow.com/training/design-custom-block-style-drift',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}