<?php declare(strict_types=1);
/**
 * Table Data Optimization Diagnostic
 *
 * Philosophy: Tables win comparison snippets
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Table_Data_Optimization {
    public static function check() {
        return [
            'id' => 'seo-table-data-optimization',
            'title' => 'Table Featured Snippet Optimization',
            'description' => 'Use HTML tables for comparison data to win table-based featured snippets.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/table-snippets/',
            'training_link' => 'https://wpshadow.com/training/comparison-tables/',
            'auto_fixable' => false,
            'threat_level' => 25,
        ];
    }
}
