<?php
declare(strict_types=1);
/**
 * Canonicalized Duplicates Internal Links Diagnostic
 *
 * Philosophy: Internal links should avoid canonicalized variants
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Canonicalized_Duplicates_Internal_Links extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-canonicalized-duplicates-internal-links',
            'title' => 'Internal Links Avoid Canonicalized Variants',
            'description' => 'Ensure internal links point to canonical versions, not duplicate URLs that canonicalize elsewhere.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/canonical-internal-linking/',
            'training_link' => 'https://wpshadow.com/training/canonicalization/',
            'auto_fixable' => false,
            'threat_level' => 15,
        ];
    }

}