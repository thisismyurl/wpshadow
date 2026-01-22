<?php declare(strict_types=1);
/**
 * X-Frame-Options Header Diagnostic
 *
 * Philosophy: Prevent clickjacking attacks
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_X_Frame_Options_Header {
    public static function check() {
        return [
            'id' => 'seo-x-frame-options-header',
            'title' => 'X-Frame-Options Header',
            'description' => 'Set X-Frame-Options to DENY or SAMEORIGIN to prevent clickjacking attacks.',
            'severity' => 'medium',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/x-frame-options/',
            'training_link' => 'https://wpshadow.com/training/clickjacking-prevention/',
            'auto_fixable' => false,
            'threat_level' => 50,
        ];
    }
}
