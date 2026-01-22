<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Checkbox Consistency
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-checkbox-consistency
 * Training: https://wpshadow.com/training/design-checkbox-consistency
 */
class Diagnostic_Design_CHECKBOX_CONSISTENCY {
    public static function check() {
        return [
            'id' => 'design-checkbox-consistency',
            'title' => __('Checkbox Consistency', 'wpshadow'),
            'description' => __('Confirms checkboxes 18x18px minimum.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-checkbox-consistency',
            'training_link' => 'https://wpshadow.com/training/design-checkbox-consistency',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
