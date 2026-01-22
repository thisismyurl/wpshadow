<?php
declare(strict_types=1);
/**
 * Consent Mode Diagnostic
 *
 * Philosophy: Privacy-friendly analytics activation
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Consent_Mode extends Diagnostic_Base {
    public static function check(): ?array {
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
