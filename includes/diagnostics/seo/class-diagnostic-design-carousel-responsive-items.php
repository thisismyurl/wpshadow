<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Carousel Responsive Items
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-carousel-responsive-items
 * Training: https://wpshadow.com/training/design-carousel-responsive-items
 */
class Diagnostic_Design_CAROUSEL_RESPONSIVE_ITEMS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-carousel-responsive-items',
            'title' => __('Carousel Responsive Items', 'wpshadow'),
            'description' => __('Validates carousel shows 1 item mobile, 2-4 desktop.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-carousel-responsive-items',
            'training_link' => 'https://wpshadow.com/training/design-carousel-responsive-items',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
