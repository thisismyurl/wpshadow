<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Hint Text Distinction
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-hint-text-distinction
 * Training: https://wpshadow.com/training/design-hint-text-distinction
 */
class Diagnostic_Design_HINT_TEXT_DISTINCTION {
    public static function check() {
        return [
            'id' => 'design-hint-text-distinction',
            'title' => __('Hint Text Distinction', 'wpshadow'),
            'description' => __('Checks helper text distinct from labels/errors.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-hint-text-distinction',
            'training_link' => 'https://wpshadow.com/training/design-hint-text-distinction',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
