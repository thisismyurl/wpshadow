<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Block Style Duplication
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-block-style-duplication
 * Training: https://wpshadow.com/training/design-block-style-duplication
 */
class Diagnostic_Design_DESIGN_BLOCK_STYLE_DUPLICATION extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-block-style-duplication',
            'title' => __('Block Style Duplication', 'wpshadow'),
            'description' => __('Detects duplicate custom block styles across files.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-block-style-duplication',
            'training_link' => 'https://wpshadow.com/training/design-block-style-duplication',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}