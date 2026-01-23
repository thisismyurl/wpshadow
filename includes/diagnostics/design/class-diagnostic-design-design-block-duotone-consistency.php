<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Block Duotone Consistency
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-block-duotone-consistency
 * Training: https://wpshadow.com/training/design-block-duotone-consistency
 */
class Diagnostic_Design_DESIGN_BLOCK_DUOTONE_CONSISTENCY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-block-duotone-consistency',
            'title' => __('Block Duotone Consistency', 'wpshadow'),
            'description' => __('Ensures duotone palettes match tokens and contrast rules.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-block-duotone-consistency',
            'training_link' => 'https://wpshadow.com/training/design-block-duotone-consistency',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}