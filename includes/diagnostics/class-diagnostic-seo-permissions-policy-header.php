<?php declare(strict_types=1);
/**
 * Permissions-Policy Header Diagnostic
 *
 * Philosophy: Control browser feature access
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Permissions_Policy_Header {
    public static function check() {
        return [
            'id' => 'seo-permissions-policy-header',
            'title' => 'Permissions-Policy Header',
            'description' => 'Configure Permissions-Policy (formerly Feature-Policy) to control browser features.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/permissions-policy/',
            'training_link' => 'https://wpshadow.com/training/feature-control/',
            'auto_fixable' => false,
            'threat_level' => 25,
        ];
    }
}
