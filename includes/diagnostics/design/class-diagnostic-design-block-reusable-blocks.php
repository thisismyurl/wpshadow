<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Reusable Block Usage
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-block-reusable-blocks
 * Training: https://wpshadow.com/training/design-block-reusable-blocks
 */
class Diagnostic_Design_BLOCK_REUSABLE_BLOCKS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-block-reusable-blocks',
            'title' => __('Reusable Block Usage', 'wpshadow'),
            'description' => __('Counts reusable blocks vs pattern instances.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-block-reusable-blocks',
            'training_link' => 'https://wpshadow.com/training/design-block-reusable-blocks',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}