<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Image Format Optimization
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-image-format-optimization
 * Training: https://wpshadow.com/training/design-image-format-optimization
 */
class Diagnostic_Design_IMAGE_FORMAT_OPTIMIZATION extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-image-format-optimization',
            'title' => __('Image Format Optimization', 'wpshadow'),
            'description' => __('Verifies images use modern formats.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-image-format-optimization',
            'training_link' => 'https://wpshadow.com/training/design-image-format-optimization',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
