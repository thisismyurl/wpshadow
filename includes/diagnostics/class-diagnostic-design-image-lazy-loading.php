<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Image Lazy Loading
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-image-lazy-loading
 * Training: https://wpshadow.com/training/design-image-lazy-loading
 */
class Diagnostic_Design_IMAGE_LAZY_LOADING {
    public static function check() {
        return [
            'id' => 'design-image-lazy-loading',
            'title' => __('Image Lazy Loading', 'wpshadow'),
            'description' => __('Confirms below-fold images lazy-loaded.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-image-lazy-loading',
            'training_link' => 'https://wpshadow.com/training/design-image-lazy-loading',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
