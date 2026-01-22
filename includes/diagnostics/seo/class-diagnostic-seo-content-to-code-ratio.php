<?php
declare(strict_types=1);
/**
 * Content to Code Ratio Diagnostic
 *
 * Philosophy: More content than HTML is better
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Content_to_Code_Ratio extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-content-to-code-ratio',
            'title' => 'Content-to-Code Ratio',
            'description' => 'Aim for 25%+ text-to-HTML ratio. Excessive HTML/JS reduces crawlability.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/content-ratio/',
            'training_link' => 'https://wpshadow.com/training/html-optimization/',
            'auto_fixable' => false,
            'threat_level' => 25,
        ];
    }
}
