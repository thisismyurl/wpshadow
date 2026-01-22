<?php declare(strict_types=1);
/**
 * Widget SEO Issues Diagnostic
 *
 * Philosophy: Widgets can harm SEO with duplicate content
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Widget_SEO_Issues {
    public static function check() {
        return [
            'id' => 'seo-widget-seo-issues',
            'title' => 'Widget SEO Review',
            'description' => 'Review sidebar widgets for duplicate content, keyword stuffing, or excessive links that can dilute page focus.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/widget-seo/',
            'training_link' => 'https://wpshadow.com/training/sidebar-optimization/',
            'auto_fixable' => false,
            'threat_level' => 15,
        ];
    }
}
