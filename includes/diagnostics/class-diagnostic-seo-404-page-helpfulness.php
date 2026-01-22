<?php declare(strict_types=1);
/**
 * 404 Page Helpfulness Diagnostic
 *
 * Philosophy: Helpful 404s retain visitors
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_404_Page_Helpfulness {
    public static function check() {
        return [
            'id' => 'seo-404-page-helpfulness',
            'title' => '404 Error Page Quality',
            'description' => 'Create helpful 404 page: search box, popular pages, navigation to retain visitors.',
            'severity' => 'medium',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/404-pages/',
            'training_link' => 'https://wpshadow.com/training/error-page-optimization/',
            'auto_fixable' => false,
            'threat_level' => 30,
        ];
    }
}
