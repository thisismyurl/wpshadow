<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Character Spacing Numbers
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-character-spacing-numbers
 * Training: https://wpshadow.com/training/design-character-spacing-numbers
 */
class Diagnostic_Design_CHARACTER_SPACING_NUMBERS {
    public static function check() {
        return [
            'id' => 'design-character-spacing-numbers',
            'title' => __('Character Spacing Numbers', 'wpshadow'),
            'description' => __('Validates numbers use monospace where needed.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-character-spacing-numbers',
            'training_link' => 'https://wpshadow.com/training/design-character-spacing-numbers',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
