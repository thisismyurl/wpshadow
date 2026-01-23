<?php
declare(strict_types=1);
/**
 * Anchor Text Distribution Natural Diagnostic
 *
 * Philosophy: Natural anchor text varies
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Anchor_Text_Distribution_Natural extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-anchor-text-distribution-natural',
            'title' => 'Anchor Text Diversity',
            'description' => 'Vary anchor text naturally. Avoid over-optimization with exact match keywords.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/anchor-text/',
            'training_link' => 'https://wpshadow.com/training/anchor-diversity/',
            'auto_fixable' => false,
            'threat_level' => 25,
        ];
    }

}