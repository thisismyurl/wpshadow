<?php
declare(strict_types=1);
/**
 * People Also Ask Optimization Diagnostic
 *
 * Philosophy: PAA boxes expand visibility
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_People_Also_Ask_Optimization extends Diagnostic_Base {
    public static function check(): ?array {
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
