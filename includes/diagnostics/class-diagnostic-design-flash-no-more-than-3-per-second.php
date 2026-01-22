<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Flash Frequency Safety
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-flash-no-more-than-3-per-second
 * Training: https://wpshadow.com/training/design-flash-no-more-than-3-per-second
 */
class Diagnostic_Design_FLASH_NO_MORE_THAN_3_PER_SECOND {
    public static function check() {
        return [
            'id' => 'design-flash-no-more-than-3-per-second',
            'title' => __('Flash Frequency Safety', 'wpshadow'),
            'description' => __('Verifies no flashing/strobing, or if present <3 Hz.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-flash-no-more-than-3-per-second',
            'training_link' => 'https://wpshadow.com/training/design-flash-no-more-than-3-per-second',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
