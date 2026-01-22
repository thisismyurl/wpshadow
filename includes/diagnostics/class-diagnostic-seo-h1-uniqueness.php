<?php declare(strict_types=1);
/**
 * H1 Uniqueness Diagnostic
 *
 * Philosophy: One primary H1 per page template
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_H1_Uniqueness {
    public static function check() {
        return [
            'id' => 'seo-h1-uniqueness',
            'title' => 'H1 Uniqueness',
            'description' => 'Ensure a single, unique H1 exists per page template to maintain clear content hierarchy.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/h1-best-practices/',
            'training_link' => 'https://wpshadow.com/training/onpage-seo/',
            'auto_fixable' => false,
            'threat_level' => 15,
        ];
    }
}
