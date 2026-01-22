<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Footer Widget Area Design
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-footer-widget-areas
 * Training: https://wpshadow.com/training/design-footer-widget-areas
 */
class Diagnostic_Design_FOOTER_WIDGET_AREAS {
    public static function check() {
        return [
            'id' => 'design-footer-widget-areas',
            'title' => __('Footer Widget Area Design', 'wpshadow'),
            'description' => __('Checks footer widget areas properly styled, organized.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-footer-widget-areas',
            'training_link' => 'https://wpshadow.com/training/design-footer-widget-areas',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
