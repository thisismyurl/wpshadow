<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Quote Typography
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-quote-typography
 * Training: https://wpshadow.com/training/design-quote-typography
 */
class Diagnostic_Design_QUOTE_TYPOGRAPHY {
    public static function check() {
        return [
            'id' => 'design-quote-typography',
            'title' => __('Quote Typography', 'wpshadow'),
            'description' => __('Validates blockquotes styled distinctly.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-quote-typography',
            'training_link' => 'https://wpshadow.com/training/design-quote-typography',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
