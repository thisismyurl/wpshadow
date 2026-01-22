<?php
declare(strict_types=1);
/**
 * Expandable Content Strategy Diagnostic
 *
 * Philosophy: Expandable content reduces clutter
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Expandable_Content_Strategy extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-expandable-content-strategy',
            'title' => 'Expandable Content (Accordions)',
            'description' => 'Use accordions/tabs for long content while ensuring crawlability (avoid hiding from bots).',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/expandable-content/',
            'training_link' => 'https://wpshadow.com/training/content-ui-patterns/',
            'auto_fixable' => false,
            'threat_level' => 20,
        ];
    }
}
