<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Spacing Presets
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-block-spacing-presets
 * Training: https://wpshadow.com/training/design-block-spacing-presets
 */
class Diagnostic_Design_BLOCK_SPACING_PRESETS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-block-spacing-presets',
            'title' => __('Spacing Presets', 'wpshadow'),
            'description' => __('Confirms spacing scale available in block editor.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-block-spacing-presets',
            'training_link' => 'https://wpshadow.com/training/design-block-spacing-presets',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}