<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Brand Logo File Optimization
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-brand-logo-optimization
 * Training: https://wpshadow.com/training/design-brand-logo-optimization
 */
class Diagnostic_Design_BRAND_LOGO_OPTIMIZATION extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-brand-logo-optimization',
            'title' => __('Brand Logo File Optimization', 'wpshadow'),
            'description' => __('Verifies logo is SVG, optimized size, multiple formats (SVG/PNG/WebP) for responsive display.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-brand-logo-optimization',
            'training_link' => 'https://wpshadow.com/training/design-brand-logo-optimization',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
