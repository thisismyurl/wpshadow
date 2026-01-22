<?php declare(strict_types=1);
/**
 * Question Format Optimization Diagnostic
 *
 * Philosophy: Voice search uses question format
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Question_Format_Optimization {
    public static function check() {
        return [
            'id' => 'seo-question-format-optimization',
            'title' => 'Question Format for Voice Search',
            'description' => 'Structure content as Q&A. Use question headings (who, what, when, where, why, how).',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/voice-search/',
            'training_link' => 'https://wpshadow.com/training/voice-optimization/',
            'auto_fixable' => false,
            'threat_level' => 25,
        ];
    }
}
