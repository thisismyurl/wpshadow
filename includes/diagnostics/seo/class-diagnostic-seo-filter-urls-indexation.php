<?php
declare(strict_types=1);
/**
 * Filter URLs Indexation Diagnostic
 *
 * Philosophy: Disallow deep parameter combinations
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Filter_URLs_Indexation extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-filter-urls-indexation',
            'title' => 'Filter URLs Indexation Control',
            'description' => 'Prevent deep filter/sort parameter combinations from being indexed to avoid crawl waste.',
            'severity' => 'medium',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/filter-indexation/',
            'training_link' => 'https://wpshadow.com/training/ecommerce-seo/',
            'auto_fixable' => false,
            'threat_level' => 40,
        ];
    }
}
