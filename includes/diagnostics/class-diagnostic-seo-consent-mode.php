<?php declare(strict_types=1);
/**
 * Consent Mode Diagnostic
 *
 * Philosophy: Privacy-friendly analytics activation
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Consent_Mode {
    public static function check() {
        return [
            'id' => 'seo-consent-mode',
            'title' => 'Consent Mode Implementation',
            'description' => 'Implement Google Consent Mode for privacy-friendly analytics and ad tracking compliance.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/consent-mode/',
            'training_link' => 'https://wpshadow.com/training/privacy-analytics/',
            'auto_fixable' => false,
            'threat_level' => 15,
        ];
    }
}
