<?php
declare(strict_types=1);
/**
 * Above Fold Content Quality Diagnostic
 *
 * Philosophy: First screen matters most
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Above_Fold_Content_Quality extends Diagnostic_Base {
    public static function check(): ?array {
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