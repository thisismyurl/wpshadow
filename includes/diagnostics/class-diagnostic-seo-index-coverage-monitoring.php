<?php declare(strict_types=1);
/**
 * Index Coverage Monitoring Diagnostic
 *
 * Philosophy: Proactive indexation issue detection
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Index_Coverage_Monitoring {
    public static function check() {
        return [
            'id' => 'seo-index-coverage-monitoring',
            'title' => 'Index Coverage Monitoring',
            'description' => 'Set up regular monitoring of Search Console index coverage reports to catch issues early.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/index-coverage-monitoring/',
            'training_link' => 'https://wpshadow.com/training/search-console/',
            'auto_fixable' => false,
            'threat_level' => 10,
        ];
    }
}
