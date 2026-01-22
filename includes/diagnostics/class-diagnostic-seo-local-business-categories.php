<?php declare(strict_types=1);
/**
 * Local Business Categories Diagnostic
 *
 * Philosophy: Use correct schema types for business category
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Local_Business_Categories {
    public static function check() {
        return [
            'id' => 'seo-local-business-categories',
            'title' => 'Local Business Schema Categories',
            'description' => 'Use the most specific LocalBusiness subtype (Restaurant, Store, etc.) for accurate categorization.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/local-business-types/',
            'training_link' => 'https://wpshadow.com/training/local-seo/',
            'auto_fixable' => false,
            'threat_level' => 15,
        ];
    }
}
