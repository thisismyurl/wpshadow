<?php
declare(strict_types=1);
/**
 * Render-Blocking CSS/JS Diagnostic
 *
 * Philosophy: Improve CWV by deferring non-critical assets
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Render_Blocking_CSS_JS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-render-blocking-css-js',
            'title' => 'Render-Blocking CSS/JS',
            'description' => 'Identify and defer or inline critical CSS/JS to reduce render-blocking resources and improve Core Web Vitals.',
            'severity' => 'medium',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/render-blocking-resources/',
            'training_link' => 'https://wpshadow.com/training/core-web-vitals/',
            'auto_fixable' => false,
            'threat_level' => 50,
        ];
    }

}