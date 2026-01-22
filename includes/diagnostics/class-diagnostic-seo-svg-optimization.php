<?php declare(strict_types=1);
/**
 * SVG Optimization Diagnostic
 *
 * Philosophy: SVGs load fast and scale infinitely
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_SVG_Optimization {
    public static function check() {
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
