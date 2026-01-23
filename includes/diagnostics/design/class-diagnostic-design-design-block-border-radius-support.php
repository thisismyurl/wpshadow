<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Block Border Radius Support
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-block-border-radius-support
 * Training: https://wpshadow.com/training/design-block-border-radius-support
 */
class Diagnostic_Design_DESIGN_BLOCK_BORDER_RADIUS_SUPPORT extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-block-border-radius-support',
            'title' => __('Block Border Radius Support', 'wpshadow'),
            'description' => __('Ensures radius controls map to the radius scale.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-block-border-radius-support',
            'training_link' => 'https://wpshadow.com/training/design-block-border-radius-support',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}