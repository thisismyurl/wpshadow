<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Responsive Preview Accuracy
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-block-responsive-preview
 * Training: https://wpshadow.com/training/design-block-responsive-preview
 */
class Diagnostic_Design_BLOCK_RESPONSIVE_PREVIEW extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-block-responsive-preview',
            'title' => __('Responsive Preview Accuracy', 'wpshadow'),
            'description' => __('Confirms block editor shows accurate mobile preview.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-block-responsive-preview',
            'training_link' => 'https://wpshadow.com/training/design-block-responsive-preview',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}