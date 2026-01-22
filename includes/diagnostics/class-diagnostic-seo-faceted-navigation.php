<?php declare(strict_types=1);
/**
 * Faceted Navigation Diagnostic
 *
 * Philosophy: Prevent crawl traps from filters/sorting
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Faceted_Navigation {
    /**
     * Advisory: ensure canonical/nofollow on faceted/filter links.
     *
     * @return array|null
     */
    public static function check() {
        return [
            'id' => 'seo-faceted-navigation',
            'title' => 'Faceted Navigation Controls',
            'description' => 'Ensure faceted navigation (filters, sort, pagination) uses canonicalization and nofollow where appropriate to avoid crawl traps.',
            'severity' => 'medium',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/faceted-navigation-seo/',
            'training_link' => 'https://wpshadow.com/training/faceted-navigation/',
            'auto_fixable' => false,
            'threat_level' => 35,
        ];
    }
}
