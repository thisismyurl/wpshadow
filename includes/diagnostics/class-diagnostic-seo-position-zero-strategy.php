<?php declare(strict_types=1);
/**
 * Position Zero Strategy Diagnostic
 *
 * Philosophy: Position zero is voice search source
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Position_Zero_Strategy {
    public static function check() {
        return [
            'id' => 'seo-position-zero-strategy',
            'title' => 'Position Zero (Featured Snippet) Strategy',
            'description' => 'Optimize content to win position zero: concise answers, proper formatting, schema.',
            'severity' => 'medium',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/position-zero/',
            'training_link' => 'https://wpshadow.com/training/snippet-strategies/',
            'auto_fixable' => false,
            'threat_level' => 35,
        ];
    }
}
