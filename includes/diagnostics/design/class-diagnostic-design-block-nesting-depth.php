<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Block Nesting Depth
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-block-nesting-depth
 * Training: https://wpshadow.com/training/design-block-nesting-depth
 */
class Diagnostic_Design_BLOCK_NESTING_DEPTH extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-block-nesting-depth',
            'title' => __('Block Nesting Depth', 'wpshadow'),
            'description' => __('Detects excessive nesting (UX issue).', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-block-nesting-depth',
            'training_link' => 'https://wpshadow.com/training/design-block-nesting-depth',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}