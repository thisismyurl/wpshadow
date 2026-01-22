<?php
declare(strict_types=1);
/**
 * Anchor Text Variety Diagnostic
 *
 * Philosophy: Avoid generic anchor text like "click here"
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Anchor_Text_Variety extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-anchor-text-variety',
            'title' => 'Anchor Text Variety',
            'description' => 'Use descriptive anchor text; avoid generic phrases like "click here" or "read more" for better context.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/anchor-text-best-practices/',
            'training_link' => 'https://wpshadow.com/training/internal-linking/',
            'auto_fixable' => false,
            'threat_level' => 15,
        ];
    }
}
