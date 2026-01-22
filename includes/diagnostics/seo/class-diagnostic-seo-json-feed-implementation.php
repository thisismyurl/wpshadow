<?php
declare(strict_types=1);
/**
 * JSON Feed Implementation Diagnostic
 *
 * Philosophy: JSON Feed is modern alternative to RSS
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_JSON_Feed_Implementation extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-json-feed-implementation',
            'title' => 'JSON Feed Support',
            'description' => 'Consider implementing JSON Feed as modern alternative to RSS/Atom for content syndication.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/json-feed/',
            'training_link' => 'https://wpshadow.com/training/modern-syndication/',
            'auto_fixable' => false,
            'threat_level' => 10,
        ];
    }
}
