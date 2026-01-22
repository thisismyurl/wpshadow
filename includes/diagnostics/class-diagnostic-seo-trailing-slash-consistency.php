<?php declare(strict_types=1);
/**
 * Trailing Slash Consistency Diagnostic
 *
 * Philosophy: URL canonicalization for clean indexation
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Trailing_Slash_Consistency {
    /**
     * Check permalink structure trailing slash consistency.
     *
     * @return array|null
     */
    public static function check() {
        $structure = get_option('permalink_structure');
        if (is_string($structure)) {
            $hasSlash = substr($structure, -1) === '/';
            // Advisory only: ensure a consistent canonical scheme
            return [
                'id' => 'seo-trailing-slash-consistency',
                'title' => 'Trailing Slash Consistency',
                'description' => $hasSlash
                    ? 'Permalink structure ends with a trailing slash. Ensure redirects canonicalize to slash style sitewide.'
                    : 'Permalink structure does not end with a trailing slash. Ensure redirects canonicalize to non-slash style sitewide.',
                'severity' => 'low',
                'category' => 'seo',
                'kb_link' => 'https://wpshadow.com/kb/trailing-slash-canonicalization/',
                'training_link' => 'https://wpshadow.com/training/url-canonicalization/',
                'auto_fixable' => false,
                'threat_level' => 30,
            ];
        }
        return null;
    }
}
