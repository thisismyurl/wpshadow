<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Inner Blocks Restrictions
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-block-inner-blocks-allowed
 * Training: https://wpshadow.com/training/design-block-inner-blocks-allowed
 */
class Diagnostic_Design_BLOCK_INNER_BLOCKS_ALLOWED extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-block-inner-blocks-allowed',
            'title' => __('Inner Blocks Restrictions', 'wpshadow'),
            'description' => __('Checks block restrictions configured (security).', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-block-inner-blocks-allowed',
            'training_link' => 'https://wpshadow.com/training/design-block-inner-blocks-allowed',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}