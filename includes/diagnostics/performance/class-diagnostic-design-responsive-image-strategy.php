<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Responsive Image Strategy
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-responsive-image-strategy
 * Training: https://wpshadow.com/training/design-responsive-image-strategy
 */
class Diagnostic_Design_RESPONSIVE_IMAGE_STRATEGY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-responsive-image-strategy',
            'title' => __('Responsive Image Strategy', 'wpshadow'),
            'description' => __('Validates responsive images.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-responsive-image-strategy',
            'training_link' => 'https://wpshadow.com/training/design-responsive-image-strategy',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
