<?php
declare(strict_types=1);
/**
 * Accessibility Score SEO Impact Diagnostic
 *
 * Philosophy: Accessibility improves UX signals
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Accessibility_Score_SEO_Impact extends Diagnostic_Base {
    public static function check(): ?array {
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
