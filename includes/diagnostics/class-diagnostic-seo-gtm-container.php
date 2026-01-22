<?php declare(strict_types=1);
/**
 * GTM Container Diagnostic
 *
 * Philosophy: Centralize tag management
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_GTM_Container {
    public static function check() {
        return [
            'id' => 'seo-gtm-container',
            'title' => 'Google Tag Manager Container',
            'description' => 'Consider using GTM for centralized tag management and ensure only one container is active.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/gtm-setup/',
            'training_link' => 'https://wpshadow.com/training/tag-management/',
            'auto_fixable' => false,
            'threat_level' => 10,
        ];
    }
}
