<?php
declare(strict_types=1);
/**
 * GeoIP Banner Opt-out Diagnostic
 *
 * Philosophy: Provide bot-friendly access despite geo banners
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_GeoIP_Banner_OptOut extends Diagnostic_Base {
    public static function check(): ?array {
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
