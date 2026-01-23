<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Plugin Output Styling
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-plugin-output-styling
 * Training: https://wpshadow.com/training/design-plugin-output-styling
 */
class Diagnostic_Design_PLUGIN_OUTPUT_STYLING extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-plugin-output-styling',
            'title' => __('Plugin Output Styling', 'wpshadow'),
            'description' => __('Detects unstyled plugin output, CSS conflicts.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-plugin-output-styling',
            'training_link' => 'https://wpshadow.com/training/design-plugin-output-styling',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}