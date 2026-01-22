<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Border Radius Consistency
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-border-radius-system
 * Training: https://wpshadow.com/training/design-border-radius-system
 */
class Diagnostic_Design_BORDER_RADIUS_SYSTEM {
    public static function check() {
        return [
            'id' => 'design-border-radius-system',
            'title' => __('Border Radius Consistency', 'wpshadow'),
            'description' => __('Verifies border-radius follows defined scale (0px, 4px, 8px, 16px, etc.) not random values.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-border-radius-system',
            'training_link' => 'https://wpshadow.com/training/design-border-radius-system',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
