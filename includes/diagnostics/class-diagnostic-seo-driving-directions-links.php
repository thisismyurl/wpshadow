<?php declare(strict_types=1);
/**
 * Driving Directions Links Diagnostic
 *
 * Philosophy: Convenience signals improve local rankings
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Driving_Directions_Links {
    public static function check() {
        return [
            'id' => 'seo-driving-directions-links',
            'title' => 'Driving Directions Links',
            'description' => 'Add direct links to driving directions (Google Maps, etc.) to improve local convenience signals.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/directions-links/',
            'training_link' => 'https://wpshadow.com/training/local-seo/',
            'auto_fixable' => false,
            'threat_level' => 10,
        ];
    }
}
