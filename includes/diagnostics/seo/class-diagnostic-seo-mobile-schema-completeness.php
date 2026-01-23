<?php
declare(strict_types=1);
/**
 * Mobile Schema Completeness Diagnostic
 *
 * Philosophy: Schema must render on mobile
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Mobile_Schema_Completeness extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-mobile-schema-completeness',
            'title' => 'Mobile Schema Rendering',
            'description' => 'Verify structured data renders correctly on mobile. Use Mobile-Friendly Test tool.',
            'severity' => 'medium',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/mobile-schema/',
            'training_link' => 'https://wpshadow.com/training/mobile-structured-data/',
            'auto_fixable' => false,
            'threat_level' => 35,
        ];
    }

}