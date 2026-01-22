<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Select Dropdown Style
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-select-dropdown-style
 * Training: https://wpshadow.com/training/design-select-dropdown-style
 */
class Diagnostic_Design_SELECT_DROPDOWN_STYLE {
    public static function check() {
        return [
            'id' => 'design-select-dropdown-style',
            'title' => __('Select Dropdown Style', 'wpshadow'),
            'description' => __('Checks custom selects styled consistently.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-select-dropdown-style',
            'training_link' => 'https://wpshadow.com/training/design-select-dropdown-style',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
