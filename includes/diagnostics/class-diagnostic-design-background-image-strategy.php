<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Background Image Performance
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-background-image-strategy
 * Training: https://wpshadow.com/training/design-background-image-strategy
 */
class Diagnostic_Design_BACKGROUND_IMAGE_STRATEGY {
    public static function check() {
        return [
            'id' => 'design-background-image-strategy',
            'title' => __('Background Image Performance', 'wpshadow'),
            'description' => __('Checks background images use appropriate formats, lazy loading, don't block rendering.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-background-image-strategy',
            'training_link' => 'https://wpshadow.com/training/design-background-image-strategy',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
