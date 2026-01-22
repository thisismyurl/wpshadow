<?php
declare(strict_types=1);
/**
 * Reciprocal Link Patterns Diagnostic
 *
 * Philosophy: Natural reciprocal links are okay
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Reciprocal_Link_Patterns extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-reciprocal-link-patterns',
            'title' => 'Reciprocal Link Analysis',
            'description' => 'Monitor reciprocal linking patterns. Excessive reciprocal links look unnatural.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/reciprocal-links/',
            'training_link' => 'https://wpshadow.com/training/link-schemes/',
            'auto_fixable' => false,
            'threat_level' => 20,
        ];
    }
}
