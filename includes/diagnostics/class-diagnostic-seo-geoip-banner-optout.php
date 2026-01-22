<?php declare(strict_types=1);
/**
 * GeoIP Banner Opt-out Diagnostic
 *
 * Philosophy: Provide bot-friendly access despite geo banners
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_GeoIP_Banner_OptOut {
    public static function check() {
        return [
            'id' => 'seo-geoip-banner-optout',
            'title' => 'GeoIP Banner Opt-out',
            'description' => 'Geo/language banners should allow bots and users to opt out and access canonical content freely.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/geoip-banners-seo/',
            'training_link' => 'https://wpshadow.com/training/international-seo/',
            'auto_fixable' => false,
            'threat_level' => 20,
        ];
    }
}
