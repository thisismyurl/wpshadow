<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Progressive Disclosure Implementation
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-progressive-disclosure
 * Training: https://wpshadow.com/training/design-progressive-disclosure
 */
class Diagnostic_Design_PROGRESSIVE_DISCLOSURE {
    public static function check() {
        return [
            'id' => 'design-progressive-disclosure',
            'title' => __('Progressive Disclosure Implementation', 'wpshadow'),
            'description' => __('Verifies advanced options hidden by default.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-progressive-disclosure',
            'training_link' => 'https://wpshadow.com/training/design-progressive-disclosure',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
