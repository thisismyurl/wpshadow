<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Date Picker Design
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-date-picker-design
 * Training: https://wpshadow.com/training/design-date-picker-design
 */
class Diagnostic_Design_DATE_PICKER_DESIGN {
    public static function check() {
        return [
            'id' => 'design-date-picker-design',
            'title' => __('Date Picker Design', 'wpshadow'),
            'description' => __('Checks date pickers show calendar.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-date-picker-design',
            'training_link' => 'https://wpshadow.com/training/design-date-picker-design',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
