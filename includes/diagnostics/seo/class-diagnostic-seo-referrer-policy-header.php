<?php
declare(strict_types=1);
/**
 * Referrer-Policy Header Diagnostic
 *
 * Philosophy: Control referrer information leakage
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Referrer_Policy_Header extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-referrer-policy-header',
            'title' => 'Referrer-Policy Header',
            'description' => 'Set Referrer-Policy to strict-origin-when-cross-origin for privacy and security.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/referrer-policy/',
            'training_link' => 'https://wpshadow.com/training/privacy-headers/',
            'auto_fixable' => false,
            'threat_level' => 30,
        ];
    }

}