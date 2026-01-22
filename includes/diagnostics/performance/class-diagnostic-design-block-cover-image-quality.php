<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Cover Block Image Quality
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-block-cover-image-quality
 * Training: https://wpshadow.com/training/design-block-cover-image-quality
 */
class Diagnostic_Design_BLOCK_COVER_IMAGE_QUALITY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-block-cover-image-quality',
            'title' => __('Cover Block Image Quality', 'wpshadow'),
            'description' => __('Validates cover images optimized, properly overlaid.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-block-cover-image-quality',
            'training_link' => 'https://wpshadow.com/training/design-block-cover-image-quality',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
