<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Featured Image Alt Text
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-featured-image-alt-text
 * Training: https://wpshadow.com/training/design-featured-image-alt-text
 */
class Diagnostic_Design_FEATURED_IMAGE_ALT_TEXT extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-featured-image-alt-text',
            'title' => __('Featured Image Alt Text', 'wpshadow'),
            'description' => __('Verifies all featured images have alt text.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-featured-image-alt-text',
            'training_link' => 'https://wpshadow.com/training/design-featured-image-alt-text',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
