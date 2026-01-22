<?php
declare(strict_types=1);
/**
 * GTM Container Diagnostic
 *
 * Philosophy: Centralize tag management
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_GTM_Container extends Diagnostic_Base {
    public static function check(): ?array {
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
