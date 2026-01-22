<?php declare(strict_types=1);
/**
 * Orphaned Page Detection Advanced Diagnostic
 *
 * Philosophy: All pages need inbound links
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Orphaned_Page_Detection_Advanced {
    public static function check() {
        return [
            'id' => 'seo-orphaned-page-detection-advanced',
            'title' => 'Orphaned Page Analysis',
            'description' => 'Identify pages with no internal links. Add contextual links to integrate them.',
            'severity' => 'medium',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/orphaned-pages/',
            'training_link' => 'https://wpshadow.com/training/site-architecture/',
            'auto_fixable' => false,
            'threat_level' => 35,
        ];
    }
}
