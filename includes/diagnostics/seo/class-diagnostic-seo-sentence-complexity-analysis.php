<?php
declare(strict_types=1);
/**
 * Sentence Complexity Analysis Diagnostic
 *
 * Philosophy: Shorter sentences improve clarity
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Sentence_Complexity_Analysis extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-sentence-complexity-analysis',
            'title' => 'Sentence Length and Complexity',
            'description' => 'Keep sentences under 20 words on average for better readability and engagement.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/sentence-structure/',
            'training_link' => 'https://wpshadow.com/training/writing-clarity/',
            'auto_fixable' => false,
            'threat_level' => 15,
        ];
    }

}