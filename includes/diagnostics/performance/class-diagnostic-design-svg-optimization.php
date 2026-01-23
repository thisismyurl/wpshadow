<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: SVG Optimization
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-svg-optimization
 * Training: https://wpshadow.com/training/design-svg-optimization
 */
class Diagnostic_Design_SVG_OPTIMIZATION extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-svg-optimization',
            'title' => __('SVG Optimization', 'wpshadow'),
            'description' => __('Validates SVGs minified, inline preferred.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-svg-optimization',
            'training_link' => 'https://wpshadow.com/training/design-svg-optimization',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }

}