<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Color Token Usage Ratio
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-color-token-usage-ratio
 * Training: https://wpshadow.com/training/design-color-token-usage-ratio
 */
class Diagnostic_Design_DESIGN_COLOR_TOKEN_USAGE_RATIO extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-color-token-usage-ratio',
            'title' => __('Color Token Usage Ratio', 'wpshadow'),
            'description' => __('Measures percentage of colors using tokens versus raw values.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-color-token-usage-ratio',
            'training_link' => 'https://wpshadow.com/training/design-color-token-usage-ratio',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}