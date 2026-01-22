<?php declare(strict_types=1);
/**
 * Above Fold Content Quality Diagnostic
 *
 * Philosophy: First screen matters most
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Above_Fold_Content_Quality {
    public static function check() {
        return [
            'id' => 'seo-above-fold-content-quality',
            'title' => 'Above-the-Fold Content Quality',
            'description' => 'Prioritize valuable content above-the-fold. Avoid excessive ads or distractions.',
            'severity' => 'medium',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/above-fold/',
            'training_link' => 'https://wpshadow.com/training/content-prioritization/',
            'auto_fixable' => false,
            'threat_level' => 40,
        ];
    }
}
