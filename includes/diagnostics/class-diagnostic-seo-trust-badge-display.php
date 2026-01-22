<?php declare(strict_types=1);
/**
 * Trust Badge Display Diagnostic
 *
 * Philosophy: Trust badges increase conversion
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Trust_Badge_Display {
    public static function check() {
        return [
            'id' => 'seo-trust-badge-display',
            'title' => 'Trust Badges and Certifications',
            'description' => 'Display trust badges: SSL seals, payment security, BBB, industry certifications.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/trust-badges/',
            'training_link' => 'https://wpshadow.com/training/trust-signals/',
            'auto_fixable' => false,
            'threat_level' => 20,
        ];
    }
}
