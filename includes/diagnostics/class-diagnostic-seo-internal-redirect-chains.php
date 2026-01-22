<?php declare(strict_types=1);
/**
 * Internal Redirect Chains Diagnostic
 *
 * Philosophy: Link directly to final URLs
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Internal_Redirect_Chains {
    public static function check() {
        return [
            'id' => 'seo-internal-redirect-chains',
            'title' => 'Internal Redirect Chains',
            'description' => 'Update internal links to point directly to final URLs, avoiding redirect chains.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/redirect-chains/',
            'training_link' => 'https://wpshadow.com/training/redirects-seo/',
            'auto_fixable' => false,
            'threat_level' => 20,
        ];
    }
}
