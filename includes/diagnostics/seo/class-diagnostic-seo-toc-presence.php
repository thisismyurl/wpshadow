<?php
declare(strict_types=1);
/**
 * TOC Presence Diagnostic
 *
 * Philosophy: Table of contents aids navigation and featured snippets
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_TOC_Presence extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-toc-presence',
            'title' => 'Table of Contents Presence',
            'description' => 'Add table of contents with anchor links to long-form content for better UX and featured snippet opportunities.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/table-of-contents/',
            'training_link' => 'https://wpshadow.com/training/onpage-seo/',
            'auto_fixable' => false,
            'threat_level' => 15,
        ];
    }

}