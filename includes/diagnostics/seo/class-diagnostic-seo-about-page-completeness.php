<?php
declare(strict_types=1);
/**
 * About Page Completeness Diagnostic
 *
 * Philosophy: About page establishes trust
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_About_Page_Completeness extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-about-page-completeness',
            'title' => 'About Page Quality',
            'description' => 'Create comprehensive About page with team, mission, credentials to establish authority.',
            'severity' => 'medium',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/about-page/',
            'training_link' => 'https://wpshadow.com/training/trust-signals/',
            'auto_fixable' => false,
            'threat_level' => 40,
        ];
    }
}
