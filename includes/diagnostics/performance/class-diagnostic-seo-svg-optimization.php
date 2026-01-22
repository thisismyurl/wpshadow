<?php
declare(strict_types=1);
/**
 * SVG Optimization Diagnostic
 *
 * Philosophy: SVGs load fast and scale infinitely
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_SVG_Optimization extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-svg-optimization',
            'title' => 'SVG Graphics Optimization',
            'description' => 'Use optimized SVGs for logos/icons: minified, accessible with title/desc elements.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/svg-optimization/',
            'training_link' => 'https://wpshadow.com/training/vector-graphics/',
            'auto_fixable' => false,
            'threat_level' => 15,
        ];
    }
}
