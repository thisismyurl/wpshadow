<?php
declare(strict_types=1);
/**
 * Subheading Distribution Diagnostic
 *
 * Philosophy: Regular subheadings improve scannability
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Subheading_Distribution extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-subheading-distribution',
            'title' => 'Subheading Frequency',
            'description' => 'Use subheadings (H2/H3) every 300-500 words to improve content scannability.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/subheadings/',
            'training_link' => 'https://wpshadow.com/training/content-structure/',
            'auto_fixable' => false,
            'threat_level' => 20,
        ];
    }

}