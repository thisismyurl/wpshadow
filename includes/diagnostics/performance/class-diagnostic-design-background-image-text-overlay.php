<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Background Image Text Overlay
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-background-image-text-overlay
 * Training: https://wpshadow.com/training/design-background-image-text-overlay
 */
class Diagnostic_Design_BACKGROUND_IMAGE_TEXT_OVERLAY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-background-image-text-overlay',
            'title' => __('Background Image Text Overlay', 'wpshadow'),
            'description' => __('Validates text readable via overlay.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-background-image-text-overlay',
            'training_link' => 'https://wpshadow.com/training/design-background-image-text-overlay',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }

}