<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Image Alt Text Descriptive
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-image-alt-text-descriptive
 * Training: https://wpshadow.com/training/design-image-alt-text-descriptive
 */
class Diagnostic_Design_IMAGE_ALT_TEXT_DESCRIPTIVE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-image-alt-text-descriptive',
            'title' => __('Image Alt Text Descriptive', 'wpshadow'),
            'description' => __('Validates all images have descriptive alt text.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-image-alt-text-descriptive',
            'training_link' => 'https://wpshadow.com/training/design-image-alt-text-descriptive',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }

}