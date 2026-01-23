<?php
declare(strict_types=1);
/**
 * Keyword in First Paragraph Diagnostic
 *
 * Philosophy: Early topic signals improve relevance
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Keyword_First_Paragraph extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-keyword-first-paragraph',
            'title' => 'Keyword in First Paragraph',
            'description' => 'Include primary keyword naturally in the first paragraph to establish topic relevance early.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/keyword-placement/',
            'training_link' => 'https://wpshadow.com/training/onpage-seo/',
            'auto_fixable' => false,
            'threat_level' => 15,
        ];
    }

}