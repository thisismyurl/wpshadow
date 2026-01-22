<?php
declare(strict_types=1);
/**
 * Paragraph Length Distribution Diagnostic
 *
 * Philosophy: Short paragraphs improve scannability
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Paragraph_Length_Distribution extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-paragraph-length-distribution',
            'title' => 'Paragraph Length for Readability',
            'description' => 'Keep paragraphs 3-4 sentences max for web readability. Break up walls of text.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/paragraph-structure/',
            'training_link' => 'https://wpshadow.com/training/web-writing/',
            'auto_fixable' => false,
            'threat_level' => 15,
        ];
    }
}
