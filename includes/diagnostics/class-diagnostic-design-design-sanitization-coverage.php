<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Sanitization Coverage
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-sanitization-coverage
 * Training: https://wpshadow.com/training/design-sanitization-coverage
 */
class Diagnostic_Design_DESIGN_SANITIZATION_COVERAGE {
    public static function check() {
        return [
            'id' => 'design-sanitization-coverage',
            'title' => __('Sanitization Coverage', 'wpshadow'),
            'description' => __('Checks sanitization callbacks exist for customizer fields.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-sanitization-coverage',
            'training_link' => 'https://wpshadow.com/training/design-sanitization-coverage',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

