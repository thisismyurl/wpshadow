<?php
declare(strict_types=1);
/**
 * HSTS Header Configuration Diagnostic
 *
 * Philosophy: HSTS enforces HTTPS
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_HSTS_Header_Configuration extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-hsts-header-configuration',
            'title' => 'HTTP Strict Transport Security (HSTS)',
            'description' => 'Enable HSTS header to enforce HTTPS and prevent downgrade attacks.',
            'severity' => 'high',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/hsts/',
            'training_link' => 'https://wpshadow.com/training/https-enforcement/',
            'auto_fixable' => false,
            'threat_level' => 65,
        ];
    }
}
