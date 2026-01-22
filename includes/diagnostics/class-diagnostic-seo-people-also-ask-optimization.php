<?php declare(strict_types=1);
/**
 * People Also Ask Optimization Diagnostic
 *
 * Philosophy: PAA boxes expand visibility
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_People_Also_Ask_Optimization {
    public static function check() {
        return [
            'id' => 'seo-people-also-ask-optimization',
            'title' => 'People Also Ask (PAA) Optimization',
            'description' => 'Research PAA questions for target keywords and create dedicated answers.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/people-also-ask/',
            'training_link' => 'https://wpshadow.com/training/paa-strategy/',
            'auto_fixable' => false,
            'threat_level' => 25,
        ];
    }
}
