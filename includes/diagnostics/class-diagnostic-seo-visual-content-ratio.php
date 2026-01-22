<?php declare(strict_types=1);
/**
 * Visual Content Ratio Diagnostic
 *
 * Philosophy: Images break up text
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Visual_Content_Ratio {
    public static function check() {
        return [
            'id' => 'seo-visual-content-ratio',
            'title' => 'Visual Content Balance',
            'description' => 'Include images every 200-300 words to maintain engagement and visual interest.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/visual-content/',
            'training_link' => 'https://wpshadow.com/training/multimedia-content/',
            'auto_fixable' => false,
            'threat_level' => 20,
        ];
    }
}
