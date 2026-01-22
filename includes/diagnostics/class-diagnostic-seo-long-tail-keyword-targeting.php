<?php declare(strict_types=1);
/**
 * Long-Tail Keyword Targeting Diagnostic
 *
 * Philosophy: Voice search uses long-tail queries
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Long_Tail_Keyword_Targeting {
    public static function check() {
        return [
            'id' => 'seo-long-tail-keyword-targeting',
            'title' => 'Long-Tail Keyword Strategy',
            'description' => 'Target long-tail keywords (4+ words) that match natural speech patterns.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/long-tail-keywords/',
            'training_link' => 'https://wpshadow.com/training/keyword-research/',
            'auto_fixable' => false,
            'threat_level' => 25,
        ];
    }
}
