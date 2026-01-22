<?php declare(strict_types=1);
/**
 * Form Usability Mobile Diagnostic
 *
 * Philosophy: Mobile forms must be easy to complete
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Form_Usability_Mobile {
    public static function check() {
        return [
            'id' => 'seo-form-usability-mobile',
            'title' => 'Mobile Form Usability',
            'description' => 'Optimize forms for mobile: appropriate input types, autocomplete, large touch targets, minimal fields.',
            'severity' => 'medium',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/mobile-forms/',
            'training_link' => 'https://wpshadow.com/training/form-optimization/',
            'auto_fixable' => false,
            'threat_level' => 40,
        ];
    }
}
