<?php declare(strict_types=1);
/**
 * oEmbed Optimization Diagnostic
 *
 * Philosophy: oEmbed enables rich previews
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_oEmbed_Optimization {
    public static function check() {
        return [
            'id' => 'seo-oembed-optimization',
            'title' => 'oEmbed Configuration',
            'description' => 'Configure oEmbed for rich previews when content is embedded on other sites.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/oembed/',
            'training_link' => 'https://wpshadow.com/training/content-embedding/',
            'auto_fixable' => false,
            'threat_level' => 10,
        ];
    }
}
