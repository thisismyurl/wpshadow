<?php
declare(strict_types=1);
/**
 * Mixed Content Diagnostic
 *
 * Philosophy: Avoid HTTP assets on HTTPS pages
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Mixed_Content extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-mixed-content',
            'title' => 'Mixed Content Risk',
            'description' => 'Ensure images, scripts and styles are served over HTTPS on HTTPS pages to prevent browser warnings and crawl issues.',
            'severity' => 'medium',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/mixed-content-fix/',
            'training_link' => 'https://wpshadow.com/training/https-best-practices/',
            'auto_fixable' => false,
            'threat_level' => 50,
        ];
    }
}
