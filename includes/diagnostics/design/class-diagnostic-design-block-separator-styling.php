<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Separator Block Styling
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-block-separator-styling
 * Training: https://wpshadow.com/training/design-block-separator-styling
 */
class Diagnostic_Design_BLOCK_SEPARATOR_STYLING extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-block-separator-styling',
            'title' => __('Separator Block Styling', 'wpshadow'),
            'description' => __('Checks separator styling matches design system.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-block-separator-styling',
            'training_link' => 'https://wpshadow.com/training/design-block-separator-styling',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}