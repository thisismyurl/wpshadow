<?php
declare(strict_types=1);
/**
 * Print-Friendly Pages Diagnostic
 *
 * Philosophy: Some users prefer printing
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Print_Friendly_Pages extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-print-friendly-pages',
            'title' => 'Print-Friendly Content',
            'description' => 'Offer print-friendly versions for longer content (guides, whitepapers).',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/print-friendly/',
            'training_link' => 'https://wpshadow.com/training/content-formats/',
            'auto_fixable' => false,
            'threat_level' => 10,
        ];
    }
}
