<?php declare(strict_types=1);
/**
 * Vary Header Configuration Diagnostic
 *
 * Philosophy: Vary header guides proxy caching
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Vary_Header_Configuration {
    public static function check() {
        return [
            'id' => 'seo-vary-header-configuration',
            'title' => 'Vary Header for Content Negotiation',
            'description' => 'Configure Vary header for Accept-Encoding, User-Agent, or other content negotiation scenarios.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/vary-header/',
            'training_link' => 'https://wpshadow.com/training/http-headers/',
            'auto_fixable' => false,
            'threat_level' => 15,
        ];
    }
}
