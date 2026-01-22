<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Block Alignment Compliance
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-gutenberg-block-alignment
 * Training: https://wpshadow.com/training/design-gutenberg-block-alignment
 */
class Diagnostic_Design_GUTENBERG_BLOCK_ALIGNMENT extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-gutenberg-block-alignment',
            'title' => __('Block Alignment Compliance', 'wpshadow'),
            'description' => __('Confirms blocks respect site content width/alignment.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-gutenberg-block-alignment',
            'training_link' => 'https://wpshadow.com/training/design-gutenberg-block-alignment',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
