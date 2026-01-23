<?php
declare(strict_types=1);
/**
 * Data Visualization Quality Diagnostic
 *
 * Philosophy: Visuals make data accessible
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Data_Visualization_Quality extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-data-visualization-quality',
            'title' => 'Data Visualization Integration',
            'description' => 'Use charts, graphs, and infographics to make data more accessible and shareable.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/data-visualization/',
            'training_link' => 'https://wpshadow.com/training/visual-content/',
            'auto_fixable' => false,
            'threat_level' => 15,
        ];
    }

}