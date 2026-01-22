<?php declare(strict_types=1);
/**
 * Canonical Primary Host Diagnostic
 *
 * Philosophy: Enforce consistent host (www vs non-www)
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Canonical_Primary_Host {
    public static function check() {
        return [
            'id' => 'seo-canonical-primary-host',
            'title' => 'Canonicalize to Primary Host',
            'description' => 'Ensure canonical redirects enforce a single host (www or non-www) sitewide to avoid duplicate indexation.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/primary-host-canonicalization/',
            'training_link' => 'https://wpshadow.com/training/redirects-seo/',
            'auto_fixable' => false,
            'threat_level' => 20,
        ];
    }
}
