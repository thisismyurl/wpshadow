<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Link Color Distinction WCAG
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-link-color-distinction-wcag
 * Training: https://wpshadow.com/training/design-link-color-distinction-wcag
 */
class Diagnostic_Design_LINK_COLOR_DISTINCTION_WCAG {
    public static function check() {
        return [
            'id' => 'design-link-color-distinction-wcag',
            'title' => __('Link Color Distinction WCAG', 'wpshadow'),
            'description' => __('Confirms links distinguishable from text.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-link-color-distinction-wcag',
            'training_link' => 'https://wpshadow.com/training/design-link-color-distinction-wcag',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
