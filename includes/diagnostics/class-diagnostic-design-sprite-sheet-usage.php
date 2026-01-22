<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Sprite Sheet Usage
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-sprite-sheet-usage
 * Training: https://wpshadow.com/training/design-sprite-sheet-usage
 */
class Diagnostic_Design_SPRITE_SHEET_USAGE {
    public static function check() {
        return [
            'id' => 'design-sprite-sheet-usage',
            'title' => __('Sprite Sheet Usage', 'wpshadow'),
            'description' => __('Checks sprite sheets used for icons.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-sprite-sheet-usage',
            'training_link' => 'https://wpshadow.com/training/design-sprite-sheet-usage',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
