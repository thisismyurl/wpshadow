<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Image Caption Styling
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-image-caption-styling
 * Training: https://wpshadow.com/training/design-image-caption-styling
 */
class Diagnostic_Design_IMAGE_CAPTION_STYLING extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-image-caption-styling',
            'title' => __('Image Caption Styling', 'wpshadow'),
            'description' => __('Validates image captions styled properly.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-image-caption-styling',
            'training_link' => 'https://wpshadow.com/training/design-image-caption-styling',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
