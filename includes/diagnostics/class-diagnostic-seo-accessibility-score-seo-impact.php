<?php declare(strict_types=1);
/**
 * Accessibility Score SEO Impact Diagnostic
 *
 * Philosophy: Accessibility improves UX signals
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Accessibility_Score_SEO_Impact {
    public static function check() {
        return [
            'id' => 'seo-accessibility-score-seo-impact',
            'title' => 'Accessibility Score and SEO',
            'description' => 'Improve accessibility score: semantic HTML, keyboard navigation, screen reader support affect user experience signals.',
            'severity' => 'medium',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/accessibility-seo/',
            'training_link' => 'https://wpshadow.com/training/web-accessibility/',
            'auto_fixable' => false,
            'threat_level' => 35,
        ];
    }
}
