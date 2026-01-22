<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Language Specification
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-language-specification
 * Training: https://wpshadow.com/training/design-language-specification
 */
class Diagnostic_Design_LANGUAGE_SPECIFICATION {
    public static function check() {
        return [
            'id' => 'design-language-specification',
            'title' => __('Language Specification', 'wpshadow'),
            'description' => __('Validates page language set (<html lang='en'>).', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-language-specification',
            'training_link' => 'https://wpshadow.com/training/design-language-specification',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
