<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: VRT Carousel Slider
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-vrt-carousel-slider
 * Training: https://wpshadow.com/training/design-vrt-carousel-slider
 */
class Diagnostic_Design_DESIGN_VRT_CAROUSEL_SLIDER extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-vrt-carousel-slider',
            'title' => __('VRT Carousel Slider', 'wpshadow'),
            'description' => __('Checks slider paddings, dots, arrows alignment, and visibility.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-vrt-carousel-slider',
            'training_link' => 'https://wpshadow.com/training/design-vrt-carousel-slider',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
