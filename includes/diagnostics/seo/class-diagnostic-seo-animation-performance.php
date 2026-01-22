<?php
declare(strict_types=1);
/**
 * Animation Performance Diagnostic
 *
 * Philosophy: Prefer transform/opacity for smooth animations
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Animation_Performance extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-animation-performance',
            'title' => 'Animation Performance Best Practices',
            'description' => 'Use transform and opacity for animations; avoid layout thrashing properties to maintain smoothness.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/animation-performance/',
            'training_link' => 'https://wpshadow.com/training/performance-seo/',
            'auto_fixable' => false,
            'threat_level' => 10,
        ];
    }
}
