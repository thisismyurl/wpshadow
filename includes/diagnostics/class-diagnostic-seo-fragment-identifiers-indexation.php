<?php declare(strict_types=1);
/**
 * Fragment Identifiers Indexation Diagnostic
 *
 * Philosophy: Hash fragments not indexed by default
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Fragment_Identifiers_Indexation {
    public static function check() {
        return [
            'id' => 'seo-fragment-identifiers-indexation',
            'title' => 'URL Fragment (#) Indexation',
            'description' => 'URL fragments (#section) are not indexed. Use History API for routing or server-side rendering.',
            'severity' => 'medium',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/url-fragments/',
            'training_link' => 'https://wpshadow.com/training/url-structure-seo/',
            'auto_fixable' => false,
            'threat_level' => 40,
        ];
    }
}
