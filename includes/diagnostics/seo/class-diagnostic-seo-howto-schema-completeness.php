<?php
declare(strict_types=1);
/**
 * HowTo Schema Completeness Diagnostic
 *
 * Philosophy: Ensure step-structured markup completeness
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_HowTo_Schema_Completeness extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-howto-schema-completeness',
            'title' => 'HowTo Schema Completeness',
            'description' => 'Ensure HowTo schema includes steps, images, and durations where applicable.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/howto-schema/',
            'training_link' => 'https://wpshadow.com/training/schema-serp-features/',
            'auto_fixable' => false,
            'threat_level' => 10,
        ];
    }
}
