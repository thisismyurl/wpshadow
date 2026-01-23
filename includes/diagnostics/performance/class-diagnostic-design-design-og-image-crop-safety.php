<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: OG Image Crop Safety
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-og-image-crop-safety
 * Training: https://wpshadow.com/training/design-og-image-crop-safety
 */
class Diagnostic_Design_DESIGN_OG_IMAGE_CROP_SAFETY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-og-image-crop-safety',
            'title' => __('OG Image Crop Safety', 'wpshadow'),
            'description' => __('Checks focal point and crop safety for OG images.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-og-image-crop-safety',
            'training_link' => 'https://wpshadow.com/training/design-og-image-crop-safety',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}